<?php

namespace Tests\Feature;

use App\Support\LegalDocumentService;
use App\Support\MetaTrackingPolicyUpdate;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class MetaTrackingPolicyUpdateTest extends TestCase
{
    use RefreshDatabase;

    public function test_policy_migration_preserves_custom_content_and_old_versions_and_is_repeatable(): void
    {
        $documents = app(LegalDocumentService::class);
        $old = $documents->publish('privacy', 'it', 'custom-policy', 'Privacy personalizzata', 'Descrizione personalizzata',
            '<p>Testo aziendale da conservare.</p><p>Alla data di questa versione, Advanced Matching e Conversions API sono disattivati.</p>');
        $migration = require database_path('migrations/2026_09_10_000002_publish_meta_conversion_policy_updates.php');
        $migration->up();
        $current = $documents->current('privacy', 'it');
        $this->assertSame('Privacy personalizzata', $current->title);
        $this->assertStringContainsString('Testo aziendale da conservare.', $current->content_snapshot);
        $this->assertStringNotContainsString('Conversions API sono disattivati', $current->content_snapshot);
        $this->assertStringContainsString('SHA-256', $current->content_snapshot);
        $this->assertDatabaseHas('legal_document_versions', ['id' => $old->version_id, 'version' => 'custom-policy']);
        $count = DB::table('legal_document_versions')->count();
        $migration->up();
        $this->assertSame($count, DB::table('legal_document_versions')->count());
        $this->assertSame($current->content_snapshot, MetaTrackingPolicyUpdate::apply($current->content_snapshot, 'it'));
    }
}
