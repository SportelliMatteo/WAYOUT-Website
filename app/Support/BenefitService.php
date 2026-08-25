<?php

namespace App\Support;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class BenefitService
{
    public function typeFor(string $waitlistEntryId): string
    {
        $plans = DB::table('purchases')
            ->where('waitlist_entry_id', $waitlistEntryId)
            ->where('status', 'succeeded')
            ->pluck('plan');

        if ($plans->contains('creator')) {
            return 'founder_creator';
        }

        if ($plans->contains('join')) {
            return 'founder_join';
        }

        return 'waitlist';
    }

    /** @return array{benefit_id: string, waitlist_position: int, benefit_type: string, claimed_at: string} */
    public function claim(object $entry, string $accountReference, string $idempotencyKey): array
    {
        return DB::transaction(function () use ($entry, $accountReference, $idempotencyKey): array {
            $lockedEntry = DB::table('waitlist_entries')->where('id', $entry->id)->lockForUpdate()->first();
            $benefitType = $this->typeFor($lockedEntry->id);
            $idempotentClaim = DB::table('benefit_claims')
                ->where('idempotency_key', $idempotencyKey)
                ->lockForUpdate()
                ->first();

            if ($idempotentClaim && ($idempotentClaim->waitlist_entry_id !== $lockedEntry->id
                || ! hash_equals($idempotentClaim->account_reference, $accountReference))) {
                throw new BenefitClaimConflict;
            }

            $claim = DB::table('benefit_claims')
                ->where('waitlist_entry_id', $lockedEntry->id)
                ->lockForUpdate()
                ->first();

            if ($claim && ! hash_equals($claim->account_reference, $accountReference)) {
                throw new BenefitClaimConflict;
            }

            if ($claim) {
                DB::table('benefit_claims')->where('id', $claim->id)->update([
                    'benefit_type' => $benefitType,
                    'updated_at' => now(),
                ]);
                $claimedAt = $claim->claimed_at;
            } else {
                $claimedAt = now();
                DatabaseUuid::insert('benefit_claims', [
                    'waitlist_entry_id' => $lockedEntry->id,
                    'account_reference' => $accountReference,
                    'idempotency_key' => $idempotencyKey,
                    'benefit_type' => $benefitType,
                    'claimed_at' => $claimedAt,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            return [
                'benefit_id' => $lockedEntry->benefit_id,
                'waitlist_position' => (int) $lockedEntry->waitlist_position,
                'benefit_type' => $benefitType,
                'claimed_at' => Carbon::parse($claimedAt)->toISOString(),
            ];
        });
    }
}
