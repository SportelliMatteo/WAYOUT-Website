<?php

use App\Support\DatabaseUuid;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        foreach (['privacy', 'cookies'] as $documentKey) {
            $document = config('legal.documents.'.$documentKey);

            foreach (['it', 'en'] as $locale) {
                $view = $locale === 'en' ? ($document['views']['en'] ?? null) : ($document['view'] ?? null);

                if (! is_string($view)) {
                    continue;
                }

                $source = file_get_contents(resource_path('views/'.$view));

                if (! is_string($source)
                    || ! preg_match("/@section\('legal-content'\)\s*(.*?)\s*@endsection/s", $source, $matches)) {
                    throw new RuntimeException('Unable to load reCAPTCHA policy update: '.$documentKey.' '.$locale);
                }

                $content = trim($matches[1]);
                $hash = hash('sha256', $content);

                DB::transaction(function () use ($documentKey, $document, $locale, $view, $content, $hash): void {
                    $sameContent = DB::table('legal_document_versions')
                        ->where('document_key', $documentKey)
                        ->where('locale', $locale)
                        ->where('content_hash', $hash)
                        ->first();
                    $versionId = $sameContent?->id;

                    if (! $versionId) {
                        $baseVersion = '2026-08-31-recaptcha';
                        $version = $baseVersion;
                        $revision = 1;

                        while (DB::table('legal_document_versions')
                            ->where('document_key', $documentKey)
                            ->where('locale', $locale)
                            ->where('version', $version)
                            ->exists()) {
                            $version = $baseVersion.'-r'.++$revision;
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

                    $existingDocument = DB::table('legal_documents')
                        ->where('document_key', $documentKey)
                        ->where('locale', $locale)
                        ->first();

                    DB::table('legal_documents')->updateOrInsert(
                        ['document_key' => $documentKey, 'locale' => $locale],
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
    }

    public function down(): void
    {
        // Legal versions are immutable audit records and are not removed on rollback.
    }
};
