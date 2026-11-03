<?php

namespace App\Http\Controllers;

use App\Exceptions\WayoutApiException;
use App\Models\WithdrawalRequest;
use App\Support\AdminAuditService;
use App\Support\DatabaseUuid;
use App\Support\WayoutApiClient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class AdminRefundController extends Controller
{
    public function store(Request $request, WithdrawalRequest $withdrawal, WayoutApiClient $wayout, AdminAuditService $audit)
    {
        $purchase = DB::table('purchases')->where('id', $withdrawal->purchase_id)->first();
        abort_unless($purchase, 404);
        $redirect = redirect()->route('admin.dashboard', ['tab' => 'withdrawals']);
        $lock = Cache::lock('purchase-refund:'.$purchase->id, 60);

        if (! $lock->get()) {
            return $redirect->withErrors(['refund' => __('experience.refund_processing')]);
        }

        try {
            $withdrawal->refresh();
            $purchase = DB::table('purchases')->where('id', $purchase->id)->first();
            if ($withdrawal->refund_status === 'succeeded' || $purchase->status === 'refunded') {
                return $redirect->with('admin_success', __('experience.refund_done'));
            }
            if ($purchase->status !== 'succeeded' || ! is_string($purchase->wayout_subscription_id) || $purchase->wayout_subscription_id === '') {
                return $redirect->withErrors(['refund' => __('experience.refund_missing')]);
            }

            $response = $wayout->internalPost('/api/v1/internal/subscriptions/refund', [
                'external_subscription_id' => $purchase->wayout_subscription_id,
            ]);
            $status = data_get($response, 'data.status');
            $refundId = data_get($response, 'data.refund_id');
            if (! is_string($refundId) || $refundId === '' || ! is_string($status)) {
                throw new WayoutApiException('Il backend non ha confermato il rimborso.', 502, 'REFUND_NOT_CONFIRMED');
            }

            DB::transaction(function () use ($request, $withdrawal, $purchase, $refundId, $status, $audit): void {
                $withdrawal->forceFill([
                    'refund_status' => $status,
                    'status' => $status === 'succeeded' ? 'completed' : 'received',
                ])->save();
                if ($status === 'succeeded') {
                    DB::table('purchases')->where('id', $purchase->id)->update(['status' => 'refunded', 'updated_at' => now()]);
                }
                DatabaseUuid::insert('withdrawal_request_events', [
                    'withdrawal_request_id' => $withdrawal->id,
                    'event_type' => 'refund.'.$status,
                    'actor_type' => 'admin',
                    'actor_id' => $request->attributes->get('admin_user')->id,
                    'metadata' => json_encode(['refund_id' => $refundId]),
                    'occurred_at' => now(), 'created_at' => now(),
                ]);
                $audit->record($request, 'purchase.refund', 'purchase', $purchase->id, $purchase->order_reference,
                    newValues: ['refund_id' => $refundId, 'status' => $status]);
            });

            return $redirect->with('admin_success', $status === 'succeeded' ? __('experience.refund_done') : __('experience.refund_processing'));
        } catch (WayoutApiException $exception) {
            $audit->record($request, 'purchase.refund_failed', 'purchase', $purchase->id, $purchase->order_reference,
                newValues: ['code' => $exception->apiCode, 'http_status' => $exception->status]);

            return $redirect->withErrors(['refund' => $exception->getMessage()]);
        } finally {
            $lock->release();
        }
    }
}
