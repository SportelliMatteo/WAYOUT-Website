<?php

namespace Tests\Feature;

use App\Support\LegalDocumentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class SellerPhonePublicationTest extends TestCase
{
    use RefreshDatabase;

    public function test_phone_publication_preserves_existing_content_and_history_and_is_idempotent(): void
    {
        $documents = app(LegalDocumentService::class);
        $previous = [];

        foreach (['sales', 'presale'] as $key) {
            foreach (['it', 'en'] as $locale) {
                $previous[] = $documents->publish($key, $locale, 'before-phone', 'Custom title', 'Custom description', '<p>Existing published content</p>');
            }
        }

        $migration = require database_path('migrations/2026_09_18_000001_publish_seller_phone.php');
        $migration->up();
        $count = DB::table('legal_document_versions')->count();
        $migration->up();
        $this->assertSame($count, DB::table('legal_document_versions')->count());

        foreach ($previous as $old) {
            $current = $documents->current($old->document_key, $old->locale);
            $this->assertStringContainsString('+393522164100', $current->content_snapshot);
            $this->assertStringContainsString($old->content_snapshot, $current->content_snapshot);
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
