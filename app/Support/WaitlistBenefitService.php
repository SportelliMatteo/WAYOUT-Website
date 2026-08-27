<?php

namespace App\Support;

use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

class WaitlistBenefitService
{
    public const DURATION_DAYS = 60;

    public function eligibleQuery(): Builder
    {
        return DB::table('waitlist_entries')
            ->select([
                'waitlist_entries.id',
                'waitlist_entries.benefit_id',
                'waitlist_entries.email',
                'waitlist_entries.waitlist_position',
                'waitlist_entries.email_verified_at',
            ])
            ->whereNotNull('waitlist_entries.email_verified_at')
            ->whereNotNull('waitlist_entries.benefit_id')
            ->whereNotNull('waitlist_entries.waitlist_position')
            ->whereNotExists(function (Builder $query): void {
                $query->selectRaw('1')
                    ->from('purchases')
                    ->whereColumn('purchases.waitlist_entry_id', 'waitlist_entries.id')
                    ->where('purchases.status', 'succeeded');
            });
    }

    public function count(): int
    {
        return $this->eligibleQuery()->count();
    }

    public function findEligibleByEmail(string $email): ?object
    {
        return $this->eligibleQuery()
            ->where('waitlist_entries.email', $email)
            ->first();
    }
}
