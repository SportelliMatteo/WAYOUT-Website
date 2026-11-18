<?php

namespace Tests\Feature;

use App\Support\DatabaseUuid;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class DeploymentUpgradePreservationTest extends TestCase
{
    protected function usesDefaultLegalFixtures(): bool
    {
        return false;
    }

    public function test_upgrade_from_waitlist_creator_migration_preserves_waitlist_consents_and_legal_archive(): void
    {
        $cutoff = '2026_09_18_000003_publish_waitlist_creator_features.php';
        $historical = collect(glob(database_path('migrations/*.php')))
            ->filter(fn ($path) => basename($path) <= $cutoff)
            ->map(fn ($path) => 'database/migrations/'.basename($path))->values()->all();
        $this->artisan('migrate', ['--path' => $historical, '--force' => true])->assertSuccessful();
        $verified = $this->createVerifiedWaitlistEntry('preserve-verified@example.com', 1);
        DatabaseUuid::insert('waitlist_entries', [
            'email' => 'preserve-pending@example.com', 'marketing_consent' => false,
            'created_at' => now()->subDays(90), 'updated_at' => now()->subDays(90),
        ]);
        DatabaseUuid::insert('consent_events', [
            'waitlist_entry_id' => $verified->id, 'subject_email' => $verified->email,
            'consent_type' => 'waitlist_legal', 'action' => 'granted', 'source' => 'historical_test',
            'document_versions' => '{"terms":"old-version"}', 'document_hashes' => '{"terms":"original-hash"}',
            'document_urls' => '{"terms":"https://example.test/historical-terms"}',
            'locale' => 'it', 'occurred_at' => now()->subDays(90), 'created_at' => now()->subDays(90),
        ]);
        DatabaseUuid::insert('cookie_consent_events', [
            'consent_id' => DatabaseUuid::new(), 'consent_version' => 1,
            'analytics' => true, 'marketing' => false, 'occurred_at' => now()->subDays(90),
        ]);
        DatabaseUuid::insert('waitlist_email_verifications', [
            'waitlist_entry_id' => $verified->id, 'token_hash' => hash('sha256', 'historical-token'),
            'expires_at' => now()->subDays(89), 'created_at' => now()->subDays(90),
        ]);

        $tables = ['waitlist_entries', 'waitlist_email_verifications', 'consent_events', 'cookie_consent_events', 'legal_documents', 'legal_document_versions'];
        $before = [];
        foreach ($tables as $table) {
            $before[$table] = DB::table($table)->orderBy('id')->get()->toJson();
        }
        $this->assertGreaterThan(0, DB::table('legal_document_versions')->count());

        $this->artisan('migrate', ['--force' => true])->assertSuccessful();

        foreach ($tables as $table) {
            $this->assertSame($before[$table], DB::table($table)->orderBy('id')->get()->toJson(), $table.' was modified by the upgrade');
        }
        $this->assertSame(8, DB::table('migrations')->where('migration', '>', pathinfo($cutoff, PATHINFO_FILENAME))->count());
    }
}
