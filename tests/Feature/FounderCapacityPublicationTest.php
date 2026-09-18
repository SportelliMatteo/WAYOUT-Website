<?php

namespace Tests\Feature;

use App\Support\LegalDocumentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class FounderCapacityPublicationTest extends TestCase
{
    use RefreshDatabase;

    public function test_capacity_publication_preserves_history_and_unrelated_content_and_is_idempotent(): void
    {
        $documents = app(LegalDocumentService::class);
        $previous = [];

        foreach (['sales', 'presale', 'passes'] as $key) {
            foreach (['it', 'en'] as $locale) {
                $noun = $locale === 'it' ? 'pass' : 'passes';
                $previous[] = $documents->publish($key, $locale, 'before-capacities', 'Custom title', 'Custom description',
                    '<p class="border-slate-200 text-slate-600">Join: 600 '.$noun.'; Creator: 200 '.$noun.'.</p><p>Reference 600; reference 200. +393522164100</p>');
            }
        }

        $migration = require database_path('migrations/2026_09_18_000002_publish_founder_pass_capacities.php');
        $migration->up();
        $count = DB::table('legal_document_versions')->count();
        $migration->up();
        $this->assertSame($count, DB::table('legal_document_versions')->count());

        foreach ($previous as $old) {
            $current = $documents->current($old->document_key, $old->locale);
            $this->assertStringContainsString('Join: 500 pass', $current->content_snapshot);
            $this->assertStringContainsString('Creator: 150 pass', $current->content_snapshot);
            $this->assertStringContainsString('border-slate-200 text-slate-600', $current->content_snapshot);
            $this->assertStringContainsString('Reference 600; reference 200. +393522164100', $current->content_snapshot);
            $this->assertSame($old->title, $current->title);
            $this->assertSame($old->description, $current->description);
            $this->assertDatabaseHas('legal_document_versions', [
                'id' => $old->version_id,
                'content_snapshot' => $old->content_snapshot,
                'content_hash' => $old->content_hash,
            ]);
        }
    }
}
