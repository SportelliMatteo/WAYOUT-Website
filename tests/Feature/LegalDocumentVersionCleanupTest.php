<?php

namespace Tests\Feature;

use App\Support\DatabaseUuid;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class LegalDocumentVersionCleanupTest extends TestCase
{
    use RefreshDatabase;

    public function test_only_current_compliance_versions_remain_on_the_august_31_baseline(): void
    {
        $before = DB::table('legal_document_versions')
            ->whereIn('id', DB::table('legal_documents')->pluck('current_version_id'))
            ->get()
            ->keyBy('id');

        DatabaseUuid::insert('legal_document_versions', [
            'document_key' => 'privacy',
            'version' => 'obsolete-test-version',
            'locale' => 'it',
            'title' => 'Versione obsoleta',
            'description' => null,
            'content_hash' => hash('sha256', 'obsolete'),
            'content_snapshot' => '<p>obsolete</p>',
            'content_format' => 'html',
            'source_path' => 'test',
            'published_at' => '2026-08-01 00:00:00',
            'created_at' => '2026-08-01 00:00:00',
        ]);

        $migration = require database_path('migrations/2026_08_30_000001_squash_legal_document_versions_to_2026_08_31.php');
        $migration->up();

        $after = DB::table('legal_document_versions')->get()->keyBy('id');

        $this->assertCount($before->count(), $after);
        $this->assertFalse($after->contains('version', 'obsolete-test-version'));

        foreach ($before as $id => $version) {
            $this->assertTrue($after->has($id));
            $this->assertSame('1.0', $after[$id]->version);
            $this->assertSame('2026-08-31 00:00:00', $after[$id]->published_at);
            $this->assertSame($version->content_hash, $after[$id]->content_hash);
            $this->assertSame($version->content_snapshot, $after[$id]->content_snapshot);
        }
    }
}
