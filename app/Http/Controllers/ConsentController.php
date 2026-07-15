<?php

namespace App\Http\Controllers;

use App\Support\ConsentAuditService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ConsentController extends Controller
{
    public function showMarketingRevocation(int $waitlist)
    {
        $entry = DB::table('waitlist_entries')->where('id', $waitlist)->first();

        abort_unless($entry, 404);

        return view('pages.consent.marketing-revoke', ['entry' => $entry]);
    }

    public function revokeMarketing(Request $request, int $waitlist, ConsentAuditService $audit)
    {
        $entry = DB::table('waitlist_entries')->where('id', $waitlist)->first();

        abort_unless($entry, 404);

        if (! $entry->marketing_consent) {
            return view('pages.consent.marketing-revoke', [
                'entry' => $entry,
                'alreadyRevoked' => true,
            ]);
        }

        $revoked = DB::transaction(function () use ($request, $entry, $audit) {
            $lockedEntry = DB::table('waitlist_entries')
                ->where('id', $entry->id)
                ->lockForUpdate()
                ->first();

            if (! $lockedEntry?->marketing_consent) {
                return false;
            }

            $latest = $audit->latestMarketingEvent($lockedEntry->id);

            DB::table('waitlist_entries')->where('id', $lockedEntry->id)->update([
                'marketing_consent' => false,
                'updated_at' => now(),
            ]);

            $audit->record(
                $request,
                $lockedEntry->email,
                'marketing',
                'revoked',
                'signed_marketing_revocation',
                ['marketing', 'privacy'],
                ['waitlist_entry_id' => $lockedEntry->id],
                revokesEventId: $latest?->action === 'granted' ? $latest->id : null,
            );

            return true;
        });

        if (! $revoked) {
            return view('pages.consent.marketing-revoke', [
                'entry' => (object) [...(array) $entry, 'marketing_consent' => false],
                'alreadyRevoked' => true,
            ]);
        }

        return view('pages.consent.marketing-revoke', [
            'entry' => (object) [...(array) $entry, 'marketing_consent' => false],
            'revoked' => true,
        ]);
    }
}
