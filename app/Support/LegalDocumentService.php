<?php

namespace App\Support;

use DOMDocument;
use DOMElement;
use DOMNode;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;
use LogicException;

class LegalDocumentService
{
    /** @return list<string> */
    public function keys(): array
    {
        return array_keys(config('legal.documents', []));
    }

    public function current(string $document, ?string $locale = null): object
    {
        $locale ??= app()->getLocale();
        $this->assertKnown($document);

        if (! Schema::hasTable('legal_documents')) {
            throw new LogicException('Legal document storage has not been migrated.');
        }

        $current = $this->currentQuery($document, $locale)->first();

        if ($current) {
            return $current;
        }

        $this->initialize($document, $locale);

        return $this->currentQuery($document, $locale)->firstOrFail();
    }

    /** @return Collection<string, object> */
    public function allCurrent(?string $locale = null): Collection
    {
        $locale ??= app()->getLocale();

        return collect($this->keys())
            ->mapWithKeys(fn (string $key) => [$key => $this->current($key, $locale)]);
    }

    /** @param list<string> $documents */
    public function versions(array $documents, ?string $locale = null): array
    {
        return collect($documents)
            ->mapWithKeys(fn (string $key) => [$key => $this->current($key, $locale)->version])
            ->all();
    }

    public function history(?string $locale = null): Collection
    {
        $locale ??= app()->getLocale();

        $this->allCurrent($locale);

        return DB::table('legal_document_versions')
            ->whereIn('document_key', $this->keys())
            ->where('locale', $locale)
            ->orderByDesc('published_at')
            ->orderByDesc('id')
            ->get()
            ->groupBy('document_key');
    }

    public function publish(
        string $document,
        string $locale,
        string $version,
        string $title,
        string $description,
        string $contentHtml,
    ): object {
        $this->assertKnown($document);
        $this->current($document, $locale);

        $version = trim($version);
        $title = trim($title);
        $description = trim($description);
        $contentHtml = $this->sanitizeHtml($contentHtml);

        if ($contentHtml === '') {
            throw ValidationException::withMessages([
                'content_html' => __('messages.admin.legal_content_required'),
            ]);
        }

        $version = $this->nextAvailableVersion($document, $locale, $version);

        DB::transaction(function () use ($document, $locale, $version, $title, $description, $contentHtml) {
            $versionId = DatabaseUuid::insert('legal_document_versions', [
                'document_key' => $document,
                'version' => $version,
                'locale' => $locale,
                'title' => $title,
                'description' => $description,
                'content_hash' => hash('sha256', $contentHtml),
                'content_snapshot' => $contentHtml,
                'content_format' => 'html',
                'source_path' => 'admin_dashboard',
                'published_at' => now(),
                'created_at' => now(),
            ]);

            DB::table('legal_documents')
                ->where('document_key', $document)
                ->where('locale', $locale)
                ->update([
                    'current_version_id' => $versionId,
                    'updated_at' => now(),
                ]);
        });

        return $this->current($document, $locale);
    }

    private function initialize(string $document, string $locale): void
    {
        $config = config('legal.documents.'.$document);
        $content = $this->initialContent($document, $locale, $config);
        $version = (string) ($config['initial_version'] ?? now()->toDateString());
        $title = (string) ($config['titles'][$locale] ?? $config['titles']['it'] ?? ucfirst($document));
        $description = (string) ($config['descriptions'][$locale] ?? $config['descriptions']['it'] ?? '');
        $hash = hash('sha256', $content);

        DB::transaction(function () use ($document, $locale, &$version, $title, $description, $content, $hash) {
            $existing = DB::table('legal_document_versions')
                ->where('document_key', $document)
                ->where('locale', $locale)
                ->where('version', $version)
                ->first();

            if ($existing && ($existing->content_format !== 'html' || ! hash_equals($existing->content_hash, $hash))) {
                $version = $this->availableImportedVersion($document, $locale, $version);
                $existing = null;
            }

            $versionId = $existing?->id ?? DatabaseUuid::insert('legal_document_versions', [
                'document_key' => $document,
                'version' => $version,
                'locale' => $locale,
                'title' => $title,
                'description' => $description,
                'content_hash' => $hash,
                'content_snapshot' => $content,
                'content_format' => 'html',
                'source_path' => 'initial_import',
                'published_at' => now(),
                'created_at' => now(),
            ]);

            DB::table('legal_documents')->updateOrInsert(
                ['document_key' => $document, 'locale' => $locale],
                [
                    'current_version_id' => $versionId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
            );
        });
    }

    private function initialContent(string $document, string $locale, array $config): string
    {
        if (isset($config['initial_content'])) {
            return $this->sanitizeHtml((string) ($config['initial_content'][$locale] ?? $config['initial_content']['it']));
        }

        $path = resource_path('views/'.$config['view']);
        $source = is_file($path) ? file_get_contents($path) : false;

        if (! is_string($source)
            || ! preg_match("/@section\('legal-content'\)\s*(.*?)\s*@endsection/s", $source, $matches)) {
            throw new LogicException('Unable to import legal document: '.$document);
        }

        return $this->sanitizeHtml($matches[1]);
    }

    private function currentQuery(string $document, string $locale)
    {
        return DB::table('legal_documents')
            ->join('legal_document_versions', 'legal_documents.current_version_id', '=', 'legal_document_versions.id')
            ->where('legal_documents.document_key', $document)
            ->where('legal_documents.locale', $locale)
            ->select([
                'legal_documents.id as document_id',
                'legal_documents.document_key',
                'legal_documents.locale',
                'legal_document_versions.id as version_id',
                'legal_document_versions.version',
                'legal_document_versions.title',
                'legal_document_versions.description',
                'legal_document_versions.content_hash',
                'legal_document_versions.content_snapshot',
                'legal_document_versions.content_format',
                'legal_document_versions.published_at',
            ]);
    }

    private function availableImportedVersion(string $document, string $locale, string $base): string
    {
        $candidate = $base.'-dashboard';
        $suffix = 1;

        while (DB::table('legal_document_versions')
            ->where('document_key', $document)
            ->where('locale', $locale)
            ->where('version', $candidate)
            ->exists()) {
            $candidate = $base.'-dashboard-'.$suffix++;
        }

        return $candidate;
    }

    private function nextAvailableVersion(string $document, string $locale, string $base): string
    {
        $candidate = $base;
        $revision = 2;

        while (DB::table('legal_document_versions')
            ->where('document_key', $document)
            ->where('locale', $locale)
            ->where('version', $candidate)
            ->exists()) {
            $candidate = $base.'.'.$revision++;
        }

        return $candidate;
    }

    private function assertKnown(string $document): void
    {
        if (! in_array($document, $this->keys(), true)) {
            abort(404);
        }
    }

    private function sanitizeHtml(string $html): string
    {
        $html = trim($html);

        if ($html === '') {
            return '';
        }

        $document = new DOMDocument('1.0', 'UTF-8');
        $previous = libxml_use_internal_errors(true);
        $document->loadHTML(
            '<?xml encoding="utf-8" ?><div id="legal-root">'.$html.'</div>',
            LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD,
        );
        libxml_clear_errors();
        libxml_use_internal_errors($previous);

        $root = $document->getElementById('legal-root');

        if (! $root) {
            return '';
        }

        $this->sanitizeNode($root);
        $result = '';

        foreach ($root->childNodes as $child) {
            $result .= $document->saveHTML($child);
        }

        return trim($result);
    }

    private function sanitizeNode(DOMNode $node): void
    {
        $allowedTags = [
            'div', 'section', 'h1', 'h2', 'h3', 'h4', 'p', 'span', 'ul', 'ol', 'li',
            'strong', 'em', 'b', 'i', 'a', 'br', 'table', 'thead', 'tbody', 'tfoot',
            'tr', 'th', 'td', 'dl', 'dt', 'dd',
        ];
        $allowedAttributes = ['class', 'href', 'target', 'rel', 'colspan', 'rowspan'];

        foreach (iterator_to_array($node->childNodes) as $child) {
            if ($child instanceof DOMElement) {
                if (! in_array(strtolower($child->tagName), $allowedTags, true)) {
                    $child->parentNode?->removeChild($child);

                    continue;
                }

                foreach (iterator_to_array($child->attributes) as $attribute) {
                    if (! in_array(strtolower($attribute->name), $allowedAttributes, true)) {
                        $child->removeAttribute($attribute->name);
                    }
                }

                if ($child->hasAttribute('href')) {
                    $href = trim($child->getAttribute('href'));

                    if (! preg_match('~^(https?://|mailto:|/|#)~i', $href)) {
                        $child->removeAttribute('href');
                    }
                }

                if ($child->getAttribute('target') === '_blank') {
                    $child->setAttribute('rel', 'noopener noreferrer');
                }
            }

            $this->sanitizeNode($child);
        }
    }
}
