<?php

use App\Support\DatabaseUuid;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $sourcePath = resource_path('views/pages/legal/cookies.blade.php');
        $source = file_get_contents($sourcePath);

        if (! is_string($source)
            || ! preg_match("/@section\('legal-content'\)\s*(.*?)\s*@endsection/s", $source, $matches)) {
            throw new RuntimeException('Unable to load the cookie policy version 1.2.');
        }

        $content = trim($matches[1]);
        $hash = hash('sha256', $content);
        $publishedAt = '2026-08-27 00:00:00';

        DB::transaction(function () use ($content, $hash, $publishedAt) {
            $version = DB::table('legal_document_versions')
                ->where('document_key', 'cookies')
                ->where('locale', 'it')
                ->where('version', '1.2')
                ->first();

            if ($version && ! hash_equals((string) $version->content_hash, $hash)) {
                throw new RuntimeException('A different cookie policy version 1.2 already exists.');
            }

            $versionId = $version?->id ?? DatabaseUuid::insert('legal_document_versions', [
                'document_key' => 'cookies',
                'version' => '1.2',
                'locale' => 'it',
                'title' => 'Cookie policy',
                'description' => 'Informativa sull’uso di cookie e strumenti simili sul sito WAYOUT.',
                'content_hash' => $hash,
                'content_snapshot' => $content,
                'content_format' => 'html',
                'source_path' => 'resources/views/pages/legal/cookies.blade.php',
                'published_at' => $publishedAt,
                'created_at' => $publishedAt,
            ]);

            $document = DB::table('legal_documents')
                ->where('document_key', 'cookies')
                ->where('locale', 'it')
                ->first();

            DB::table('legal_documents')->updateOrInsert(
                ['document_key' => 'cookies', 'locale' => 'it'],
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
