<?php

use App\Support\DatabaseUuid;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $source = file_get_contents(resource_path('views/pages/legal/passes.blade.php'));

        if (! is_string($source)
            || ! preg_match("/@section\('legal-content'\)\s*(.*?)\s*@endsection/s", $source, $matches)) {
            throw new RuntimeException('Unable to load the Passes information dated 2026-08-27.');
        }

        $content = trim($matches[1]);
        $hash = hash('sha256', $content);
        $publishedAt = '2026-08-27 00:00:00';

        DB::transaction(function () use ($content, $hash, $publishedAt) {
            $version = DB::table('legal_document_versions')
                ->where('document_key', 'passes')
                ->where('locale', 'it')
                ->where('version', '2026-08-27')
                ->first();

            if ($version && ! hash_equals((string) $version->content_hash, $hash)) {
                throw new RuntimeException('A different Passes information version 2026-08-27 already exists.');
            }

            $versionId = $version?->id ?? DatabaseUuid::insert('legal_document_versions', [
                'document_key' => 'passes',
                'version' => '2026-08-27',
                'locale' => 'it',
                'title' => 'Come funzionano i Pass',
                'description' => 'Waitlist Pass, Founder Join 12M e Founder Creator 12M: cosa includono, quando si attivano e quali condizioni si applicano.',
                'content_hash' => $hash,
                'content_snapshot' => $content,
                'content_format' => 'html',
                'source_path' => 'resources/views/pages/legal/passes.blade.php',
                'published_at' => $publishedAt,
                'created_at' => $publishedAt,
            ]);

            $document = DB::table('legal_documents')
                ->where('document_key', 'passes')
                ->where('locale', 'it')
                ->first();

            DB::table('legal_documents')->updateOrInsert(
                ['document_key' => 'passes', 'locale' => 'it'],
                [
                    'current_version_id' => $versionId,
                    'updated_at' => $publishedAt,
                    ...($document ? [] : [
                        'id' => DatabaseUuid::new(),
                        'created_at' => $publishedAt,
                    ]),
                ],
            );
        });
    }

    public function down(): void
    {
        // Legal versions are immutable audit records and are not removed on rollback.
    }
};
