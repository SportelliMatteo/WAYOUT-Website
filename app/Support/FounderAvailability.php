<?php

namespace App\Support;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Throwable;

class FounderAvailability
{
    public function capacities(): array
    {
        $defaults = config('founder.default_capacities');

        try {
            if (! Schema::hasTable('founder_settings')) {
                return $defaults;
            }

            return array_replace(
                $defaults,
                DB::table('founder_settings')
                    ->pluck('value', 'key')
                    ->map(fn ($value) => (int) $value)
                    ->all()
            );
        } catch (Throwable) {
            return $defaults;
        }
    }

    public function summary(): array
    {
        $capacities = $this->capacities();
        $waitlistCount = $this->waitlistCount();
        $joinCount = $this->planCount('join');
        $creatorCount = $this->planCount('creator');

        return [
            'capacities' => $capacities,
            'waitlist' => $this->entrySummary($waitlistCount, $capacities['waitlist_capacity']),
            'join' => $this->entrySummary($joinCount, $capacities['join_capacity']),
            'creator' => $this->entrySummary($creatorCount, $capacities['creator_capacity']),
        ];
    }

    public function isWaitlistFull(): bool
    {
        $capacity = $this->capacities()['waitlist_capacity'];

        return $capacity <= 0 || $this->waitlistCount() >= $capacity;
    }

    public function isPlanSoldOut(string $plan): bool
    {
        $capacity = $this->capacities()[$this->capacityKeyForPlan($plan)];

        return $capacity <= 0 || $this->planCount($plan) >= $capacity;
    }

    public function waitlistCount(): int
    {
        return DB::table('waitlist_entries')->count();
    }

    public function planCount(string $plan): int
    {
        return DB::table('purchases')
            ->where('status', 'succeeded')
            ->where('plan', $plan)
            ->count();
    }

    public function capacityKeyForPlan(string $plan): string
    {
        return $plan === 'creator' ? 'creator_capacity' : 'join_capacity';
    }

    private function entrySummary(int $used, int $capacity): array
    {
        $remaining = max(0, $capacity - $used);

        return [
            'used' => $used,
            'capacity' => $capacity,
            'remaining' => $remaining,
            'is_full' => $capacity <= 0 || $remaining <= 0,
        ];
    }
}
