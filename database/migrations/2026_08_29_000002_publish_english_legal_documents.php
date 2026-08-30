<?php

use App\Support\DatabaseUuid;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        foreach (config('legal.documents', []) as $documentKey => $document) {
            $view = $document['views']['en'] ?? null;

            if (! is_string($view)) {
                continue;
            }

            $sourcePath = resource_path('views/'.$view);
            $source = file_get_contents($sourcePath);

            if (! is_string($source)
                || ! preg_match("/@section\('legal-content'\)\s*(.*?)\s*@endsection/s", $source, $matches)) {
                throw new RuntimeException('Unable to load English legal document: '.$documentKey);
            }

            $content = trim($matches[1]);
            $hash = hash('sha256', $content);
            $baseVersion = (string) $document['initial_version'];
            $title = (string) $document['titles']['en'];
            $description = (string) $document['descriptions']['en'];

            DB::transaction(function () use ($documentKey, $view, $content, $hash, $baseVersion, $title, $description) {
                $version = $baseVersion;
                $revision = 1;

                while ($existing = DB::table('legal_document_versions')
                    ->where('document_key', $documentKey)
                    ->where('locale', 'en')
                    ->where('version', $version)
                    ->first()) {
                    if (hash_equals((string) $existing->content_hash, $hash)) {
                        $versionId = $existing->id;
                        break;
                    }

                    $version = $baseVersion.'-en'.($revision === 1 ? '' : '-'.$revision);
                    $revision++;
                }

                $versionId ??= DatabaseUuid::insert('legal_document_versions', [
                    'document_key' => $documentKey,
                    'version' => $version,
                    'locale' => 'en',
                    'title' => $title,
                    'description' => $description,
                    'content_hash' => $hash,
                    'content_snapshot' => $content,
                    'content_format' => 'html',
                    'source_path' => 'resources/views/'.$view,
                    'published_at' => now(),
                    'created_at' => now(),
                ]);

                $existingDocument = DB::table('legal_documents')
                    ->where('document_key', $documentKey)
                    ->where('locale', 'en')
                    ->exists();

                DB::table('legal_documents')->updateOrInsert(
                    ['document_key' => $documentKey, 'locale' => 'en'],
                    [
                        'current_version_id' => $versionId,
                        'updated_at' => now(),
                        ...($existingDocument ? [] : [
                            'id' => DatabaseUuid::new(),
                            'created_at' => now(),
                        ]),
                    ],
                );
            });
        }
    }

    public function down(): void
    {
        // Legal versions are immutable audit records and are not removed on rollback.
    }
};
