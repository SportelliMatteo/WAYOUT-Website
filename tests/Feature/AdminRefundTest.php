<?php

namespace Tests\Feature;

use App\Models\AdminUser;
use App\Models\WithdrawalRequest;
use App\Support\DatabaseUuid;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class AdminRefundTest extends TestCase
{
    use RefreshDatabase;

    public function test_refund_requires_admin_authentication(): void
    {
        $withdrawal = $this->withdrawal();
        Http::fake();
        $this->post(route('admin.withdrawals.refund', $withdrawal))->assertRedirect(route('admin.login'));
        Http::assertNothingSent();
    }

    public function test_refund_updates_order_only_after_backend_success_and_is_repeatable(): void
    {
        $withdrawal = $this->withdrawal();
        Http::fake(['*/internal/subscriptions/refund' => Http::response(['data' => ['refund_id' => 're_123', 'status' => 'succeeded']])]);
        $this->withSession($this->adminSession())->post(route('admin.withdrawals.refund', $withdrawal))->assertRedirect();
        $this->assertDatabaseHas('purchases', ['id' => $withdrawal->purchase_id, 'status' => 'refunded']);
        $this->assertDatabaseHas('withdrawal_requests', ['id' => $withdrawal->id, 'refund_status' => 'succeeded', 'status' => 'completed']);
        $this->assertDatabaseHas('withdrawal_request_events', ['withdrawal_request_id' => $withdrawal->id, 'event_type' => 'refund.succeeded']);
        $this->post(route('admin.withdrawals.refund', $withdrawal))->assertRedirect();
        Http::assertSentCount(1);
        Http::assertSent(fn ($request) => $request->data() === ['external_subscription_id' => 'sub_123'] && $request->hasHeader('X-Wayout-Signature'));
    }

    public function test_backend_refusal_preserves_purchase_and_displays_message(): void
    {
        $withdrawal = $this->withdrawal();
        Http::fake(['*/internal/subscriptions/refund' => Http::response(['message' => 'FUORI_FINESTRA', 'code' => 'REFUND_NOT_ALLOWED'], 422)]);
        $this->withSession($this->adminSession())->post(route('admin.withdrawals.refund', $withdrawal))->assertSessionHasErrors(['refund' => 'FUORI_FINESTRA']);
        $this->assertDatabaseHas('purchases', ['id' => $withdrawal->purchase_id, 'status' => 'succeeded']);
        $this->assertDatabaseHas('withdrawal_requests', ['id' => $withdrawal->id, 'refund_status' => 'pending']);
    }

    public function test_network_failure_allows_retry_without_marking_order_refunded(): void
    {
        $withdrawal = $this->withdrawal();
        Http::fake(['*' => Http::sequence()->pushFailedConnection()->push(['data' => ['refund_id' => 're_retry', 'status' => 'succeeded']])]);
        $this->withSession($this->adminSession())->post(route('admin.withdrawals.refund', $withdrawal))->assertSessionHasErrors('refund');
        $this->assertDatabaseHas('purchases', ['id' => $withdrawal->purchase_id, 'status' => 'succeeded']);
        $this->post(route('admin.withdrawals.refund', $withdrawal))->assertSessionHas('admin_success');
        $this->assertDatabaseHas('purchases', ['id' => $withdrawal->purchase_id, 'status' => 'refunded']);
    }

    private function adminSession(): array
    {
        $admin = AdminUser::query()->create(['name' => 'Admin', 'email' => 'refund-admin@example.com', 'password' => 'testing-password', 'is_active' => true, 'totp_confirmed_at' => now()]);

        return ['admin_authenticated' => true, 'admin_user_id' => $admin->id, 'admin_auth_version' => $admin->auth_version];
    }

    private function withdrawal(): WithdrawalRequest
    {
        config()->set('services.wayout.base_url', 'https://staging-app.wayoutapp.test');
        config()->set('services.wayout.internal_secret', str_repeat('s', 64));
        $purchaseId = DatabaseUuid::insert('purchases', ['email' => 'buyer@example.com', 'plan' => 'join', 'amount' => 2990, 'currency' => 'eur', 'status' => 'succeeded', 'wayout_subscription_id' => 'sub_123', 'order_reference' => 'WYO-REFUND', 'created_at' => now(), 'updated_at' => now()]);

        return WithdrawalRequest::query()->create([
            'purchase_id' => $purchaseId, 'receipt_number' => 'WR-TEST', 'public_token_hash' => hash('sha256', 'token'), 'idempotency_key' => DatabaseUuid::new(),
            'first_name' => 'Ada', 'last_name' => 'Lovelace', 'purchase_email' => 'buyer@example.com', 'receipt_email' => 'buyer@example.com',
            'order_reference' => 'WYO-REFUND', 'plan' => 'join', 'declaration' => 'Recesso', 'document_versions' => [], 'document_hashes' => [], 'submitted_at' => now(),
        ]);
    }
}
