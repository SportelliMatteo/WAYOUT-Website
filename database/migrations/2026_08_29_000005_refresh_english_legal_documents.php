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

            $source = file_get_contents(resource_path('views/'.$view));

            if (! is_string($source)
                || ! preg_match("/@section\('legal-content'\)\s*(.*?)\s*@endsection/s", $source, $matches)) {
                throw new RuntimeException('Unable to load English legal document: '.$documentKey);
            }

            $content = trim($matches[1]);
            $hash = hash('sha256', $content);
            $baseVersion = (string) $document['initial_version'];

            DB::transaction(function () use ($documentKey, $document, $view, $content, $hash, $baseVersion): void {
                $sameVersion = DB::table('legal_document_versions')
                    ->where('document_key', $documentKey)
                    ->where('locale', 'en')
                    ->where('content_hash', $hash)
                    ->first();

                $versionId = $sameVersion?->id;

                if (! $versionId) {
                    $version = $baseVersion.'-en-r2';
                    $revision = 2;

                    while (DB::table('legal_document_versions')
                        ->where('document_key', $documentKey)
                        ->where('locale', 'en')
                        ->where('version', $version)
                        ->exists()) {
                        $version = $baseVersion.'-en-r'.++$revision;
                    }

                    $versionId = DatabaseUuid::insert('legal_document_versions', [
                        'document_key' => $documentKey,
                        'version' => $version,
                        'locale' => 'en',
                        'title' => (string) $document['titles']['en'],
                        'description' => (string) $document['descriptions']['en'],
                        'content_hash' => $hash,
                        'content_snapshot' => $content,
                        'content_format' => 'html',
                        'source_path' => 'resources/views/'.$view,
                        'published_at' => now(),
                        'created_at' => now(),
                    ]);
                }

                DB::table('legal_documents')
                    ->where('document_key', $documentKey)
                    ->where('locale', 'en')
                    ->update([
                        'current_version_id' => $versionId,
                        'updated_at' => now(),
                    ]);
            });
        }
    }

    public function down(): void
    {
        // Legal versions are immutable audit records and are not removed on rollback.
    }
};
