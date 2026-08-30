<?php

use App\Support\DatabaseUuid;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $documentKey = 'withdrawal_info';
        $document = config('legal.documents.'.$documentKey);

        foreach (['it', 'en'] as $locale) {
            $view = $document['views'][$locale] ?? $document['view'];
            $source = file_get_contents(resource_path('views/'.$view));

            if (! is_string($source)
                || ! preg_match("/@section\('legal-content'\)\s*(.*?)\s*@endsection/s", $source, $matches)) {
                throw new RuntimeException('Unable to load '.$locale.' withdrawal information.');
            }

            $content = trim($matches[1]);
            $hash = hash('sha256', $content);

            DB::transaction(function () use ($documentKey, $document, $locale, $view, $content, $hash): void {
                $existing = DB::table('legal_document_versions')
                    ->where('document_key', $documentKey)
                    ->where('locale', $locale)
                    ->where('content_hash', $hash)
                    ->first();
                $versionId = $existing?->id;

                if (! $versionId) {
                    $base = (string) $document['initial_version'].'-responsive';
                    $version = $base;
                    $revision = 2;

                    while (DB::table('legal_document_versions')
                        ->where('document_key', $documentKey)
                        ->where('locale', $locale)
                        ->where('version', $version)
                        ->exists()) {
                        $version = $base.'-'.$revision++;
                    }

                    $versionId = DatabaseUuid::insert('legal_document_versions', [
                        'document_key' => $documentKey,
                        'version' => $version,
                        'locale' => $locale,
                        'title' => (string) $document['titles'][$locale],
                        'description' => (string) $document['descriptions'][$locale],
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
                    ->where('locale', $locale)
                    ->update([
                        'current_version_id' => $versionId,
                        'updated_at' => now(),
                    ]);
            });
        }
    }

    public function down(): void
    {
        // Published legal versions are immutable and remain available in the audit history.
    }
};
