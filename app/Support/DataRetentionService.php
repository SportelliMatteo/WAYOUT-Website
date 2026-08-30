<?php

namespace App\Support;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

final class DataRetentionService
{
    public const SCHEDULE_MINUTE = 20;

    /** @return array<string, array{cutoff: Carbon, count: int}> */
    public function preview(?Carbon $reference = null): array
    {
        $reference ??= now();

        return [
            'email_verifications' => [
                'cutoff' => $reference->copy(),
                'count' => $this->emailVerificationQuery($reference)->count(),
            ],
            'unverified_waitlist' => [
                'cutoff' => $reference->copy()->subDays(30),
                'count' => $this->unverifiedWaitlistQuery($reference)->count(),
            ],
            'admin_audit_events' => [
                'cutoff' => $reference->copy()->subYear(),
                'count' => $this->adminAuditQuery($reference)->count(),
            ],
        ];
    }

    public function run(bool $dryRun = false, string $trigger = 'scheduled'): object
    {
        $reference = now();
        $runId = DatabaseUuid::insert('data_retention_runs', [
            'trigger' => $trigger,
            'dry_run' => $dryRun,
            'status' => 'running',
            'started_at' => $reference,
            'created_at' => $reference,
        ]);

        try {
            $results = DB::transaction(function () use ($dryRun, $reference): array {
                return [
                    'email_verifications' => $this->applyRule(
                        $this->emailVerificationQuery($reference),
                        $reference,
                        $dryRun,
                    ),
                    'unverified_waitlist' => $this->applyRule(
                        $this->unverifiedWaitlistQuery($reference),
                        $reference->copy()->subDays(30),
                        $dryRun,
                    ),
                    'admin_audit_events' => $this->applyRule(
                        $this->adminAuditQuery($reference),
                        $reference->copy()->subYear(),
                        $dryRun,
                    ),
                ];
            });

            DB::table('data_retention_runs')->where('id', $runId)->update([
                'status' => 'completed',
                'results' => json_encode($results, JSON_THROW_ON_ERROR),
                'completed_at' => now(),
            ]);
        } catch (Throwable $exception) {
            DB::table('data_retention_runs')->where('id', $runId)->update([
                'status' => 'failed',
                'error_message' => 'Esecuzione non completata; consultare il log applicativo.',
                'completed_at' => now(),
            ]);

            Log::error('Data retention execution failed.', [
                'run_id' => $runId,
                ...PrivacySafeLogContext::exception($exception),
            ]);
        }

        return DB::table('data_retention_runs')->where('id', $runId)->first();
    }

    public function nextRunAt(?Carbon $reference = null): Carbon
    {
        $timezone = (string) config('app.display_timezone', 'Europe/Rome');
        $local = ($reference ?? now())->copy()->setTimezone($timezone);
        $next = $local->copy()->minute(self::SCHEDULE_MINUTE)->second(0)->microsecond(0);

        if ($next->lessThanOrEqualTo($local)) {
            $next->addHour();
        }

        return $next->utc();
    }

    private function emailVerificationQuery(Carbon $reference)
    {
        return DB::table('waitlist_email_verifications')
            ->where(function ($query) use ($reference): void {
                $query->where('expires_at', '<=', $reference)
                    ->orWhere('consumed_at', '<=', $reference);
            });
    }

    private function unverifiedWaitlistQuery(Carbon $reference)
    {
        return DB::table('waitlist_entries')
            ->whereNull('email_verified_at')
            ->where('created_at', '<=', $reference->copy()->subDays(30));
    }

    private function adminAuditQuery(Carbon $reference)
    {
        return DB::table('admin_audit_events')
            ->where('occurred_at', '<=', $reference->copy()->subYear());
    }

    /** @return array{matched: int, affected: int, cutoff: string} */
    private function applyRule($query, Carbon $cutoff, bool $dryRun): array
    {
        $matched = (clone $query)->count();

        return [
            'matched' => $matched,
            'affected' => $dryRun ? 0 : $query->delete(),
            'cutoff' => $cutoff->toISOString(),
        ];
    }
}
