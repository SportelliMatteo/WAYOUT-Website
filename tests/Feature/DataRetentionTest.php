<?php

namespace Tests\Feature;

use App\Support\DatabaseUuid;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class DataRetentionTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    public function test_retention_command_deletes_only_expired_records(): void
    {
        Carbon::setTestNow('2026-08-29 12:00:00');

        $verifiedEntry = $this->createVerifiedWaitlistEntry();
        $oldUnverifiedId = $this->insertUnverifiedEntry('old@example.com', now()->subDays(31));
        $recentUnverifiedId = $this->insertUnverifiedEntry('recent@example.com', now()->subDays(29));

        $expiredTokenId = $this->insertVerification($verifiedEntry->id, now()->subMinute());
        $consumedTokenId = $this->insertVerification($verifiedEntry->id, now()->addDay(), now()->subMinute());
        $validTokenId = $this->insertVerification($verifiedEntry->id, now()->addDay());
        $cascadedTokenId = $this->insertVerification($oldUnverifiedId, now()->addDay());

        $oldAuditId = $this->insertAdminAudit(now()->subYear()->subMinute());
        $recentAuditId = $this->insertAdminAudit(now()->subYear()->addMinute());

        $this->artisan('privacy:enforce-retention')->assertSuccessful();

        $this->assertDatabaseMissing('waitlist_email_verifications', ['id' => $expiredTokenId]);
        $this->assertDatabaseMissing('waitlist_email_verifications', ['id' => $consumedTokenId]);
        $this->assertDatabaseHas('waitlist_email_verifications', ['id' => $validTokenId]);
        $this->assertDatabaseMissing('waitlist_email_verifications', ['id' => $cascadedTokenId]);
        $this->assertDatabaseMissing('waitlist_entries', ['id' => $oldUnverifiedId]);
        $this->assertDatabaseHas('waitlist_entries', ['id' => $recentUnverifiedId]);
        $this->assertDatabaseMissing('admin_audit_events', ['id' => $oldAuditId]);
        $this->assertDatabaseHas('admin_audit_events', ['id' => $recentAuditId]);

        $run = DB::table('data_retention_runs')->first();
        $results = json_decode($run->results, true, flags: JSON_THROW_ON_ERROR);

        $this->assertSame('completed', $run->status);
        $this->assertFalse((bool) $run->dry_run);
        $this->assertSame(2, $results['email_verifications']['affected']);
        $this->assertSame(1, $results['unverified_waitlist']['affected']);
        $this->assertSame(1, $results['admin_audit_events']['affected']);
    }

    public function test_dry_run_records_counts_without_deleting_data(): void
    {
        Carbon::setTestNow('2026-08-29 12:00:00');
        $entryId = $this->insertUnverifiedEntry('old@example.com', now()->subDays(31));

        $this->artisan('privacy:enforce-retention', ['--dry-run' => true])->assertSuccessful();

        $this->assertDatabaseHas('waitlist_entries', ['id' => $entryId]);

        $run = DB::table('data_retention_runs')->first();
        $results = json_decode($run->results, true, flags: JSON_THROW_ON_ERROR);

        $this->assertTrue((bool) $run->dry_run);
        $this->assertSame('manual', $run->trigger);
        $this->assertSame(1, $results['unverified_waitlist']['matched']);
        $this->assertSame(0, $results['unverified_waitlist']['affected']);
    }

    private function insertUnverifiedEntry(string $email, Carbon $createdAt): string
    {
        $id = DatabaseUuid::new();
        DB::table('waitlist_entries')->insert([
            'id' => $id,
            'email' => $email,
            'email_verified_at' => null,
            'marketing_consent' => false,
            'created_at' => $createdAt,
            'updated_at' => $createdAt,
        ]);

        return $id;
    }

    private function insertVerification(string $entryId, Carbon $expiresAt, ?Carbon $consumedAt = null): string
    {
        $id = DatabaseUuid::new();
        DB::table('waitlist_email_verifications')->insert([
            'id' => $id,
            'waitlist_entry_id' => $entryId,
            'token_hash' => hash('sha256', $id),
            'expires_at' => $expiresAt,
            'consumed_at' => $consumedAt,
            'created_at' => now()->subHour(),
        ]);

        return $id;
    }

    private function insertAdminAudit(Carbon $occurredAt): string
    {
        return DatabaseUuid::insert('admin_audit_events', [
            'admin_user_id' => null,
            'actor_name' => 'Retention test',
            'actor_email' => 'retention@example.com',
            'action' => 'test.event',
            'target_type' => 'test',
            'occurred_at' => $occurredAt,
            'created_at' => $occurredAt,
        ]);
    }
}
