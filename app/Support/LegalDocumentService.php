<?php

namespace App\Support;

use App\Exceptions\WayoutApiException;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

/** Published documents are owned and served by the WAYOUT backend. */
class LegalDocumentService
{
    private array $currentDocuments = [];

    private array $publishedDocuments = [];

    public function __construct(private readonly WayoutApiClient $client) {}

    public static function route(string $name, array $parameters = []): string
    {
        $scope = strtoupper($parameters['scope'] ?? 'SITE');
        if (isset($parameters['document'])) {
            $document = $parameters['document'];
            $code = $scope === 'SITE' ? config('legal.backend_codes.'.$document, $document) : $document;
            $parameters['document'] = strtolower(str_replace('_', '-', $code));
        }
        $parameters['scope'] = strtolower($scope);
        $parameters['lang'] ??= app()->getLocale();

        return route($name, $parameters);
    }

    public function keys(): array
    {
        return array_keys(config('legal.backend_codes', []));
    }

    public function published(?string $scope = 'SITE'): array
    {
        $this->assertScope($scope);
        $key = $scope ?? 'ALL';
        if (isset($this->publishedDocuments[$key])) {
            return $this->publishedDocuments[$key];
        }
        if ($scope !== null && isset($this->publishedDocuments['ALL'])) {
            return array_values(array_filter($this->publishedDocuments['ALL'], fn ($document) => $document['scope'] === $scope));
        }
        $response = $this->client->get('/api/v1/legal/documents'.($scope ? '?scope='.$scope : ''));

        return $this->publishedDocuments[$key] = array_values(array_filter($response['data'] ?? [], fn ($document) => is_array($document) && in_array($document['scope'] ?? null, $scope ? [$scope] : ['APP', 'SITE'], true)
            && is_string($document['code'] ?? null) && is_string($document['name'] ?? null) && is_array($document['current'] ?? null)
        ));
    }

    public function current(string $document, ?string $locale = null, string $scope = 'SITE'): object
    {
        $locale ??= app()->getLocale();
        $this->assertScope($scope);
        $code = $this->code($document, $scope);
        $key = $scope.':'.$code.':'.$locale;
        if (isset($this->currentDocuments[$key])) {
            return $this->currentDocuments[$key];
        }
        $response = $this->client->get('/api/v1/legal/documents/'.rawurlencode($code).'?scope='.$scope, ['Accept-Language' => $locale]);
        $data = $response['data'] ?? [];
        if (($data['scope'] ?? null) !== $scope || ($data['code'] ?? null) !== $code || ! is_array($data['current'] ?? null)) {
            throw new WayoutApiException('Il documento legale SITE non è disponibile.', 503, 'LEGAL_DOCUMENT_UNAVAILABLE');
        }

        return $this->currentDocuments[$key] = $this->normalize($document, $locale, $data, $data['current'], $scope);
    }

    public function allCurrent(?string $locale = null): Collection
    {
        $published = collect($this->published())->keyBy('code');

        return collect($this->keys())->filter(fn ($key) => $published->has($this->code($key)))
            ->mapWithKeys(fn ($key) => [$key => $this->current($key, $locale)]);
    }

    public function versions(array $documents, ?string $locale = null): array
    {
        return collect($documents)->mapWithKeys(fn ($key) => [$key => $this->current($key, $locale)->version])->all();
    }

    public function history(string $document, string $scope = 'SITE'): array
    {
        $this->assertScope($scope);
        $response = $this->client->get('/api/v1/legal/documents/'.rawurlencode($this->code($document, $scope)).'/versions?scope='.$scope);

        $data = $response['data'] ?? [];
        if (($data['code'] ?? null) !== $this->code($document, $scope) || ($data['scope'] ?? null) !== $scope || ! is_array($data['versions'] ?? null)) {
            throw new WayoutApiException('Lo storico del documento SITE non è disponibile.', 503, 'LEGAL_HISTORY_UNAVAILABLE');
        }
        $currentId = $this->current($document, scope: $scope)->version_id;

        return array_values(array_filter($data['versions'], fn ($version) => is_array($version) && ($version['id'] ?? null) !== $currentId));
    }

    public function version(string $document, string $id, ?string $locale = null, string $scope = 'SITE'): object
    {
        abort_unless(Str::isUuid($id), 404);
        $current = $this->current($document, $locale, $scope);
        if ($current->version_id === $id) {
            return $current;
        }
        // Only IDs listed as previously effective for this SITE document are eligible.
        abort_unless(collect($this->history($document, $scope))->contains(fn ($version) => ($version['id'] ?? null) === $id), 404);
        $response = $this->client->get('/api/v1/legal/versions/'.rawurlencode($id), ['Accept-Language' => $locale ?? app()->getLocale()]);
        $version = $response['data'] ?? [];
        if (($version['id'] ?? null) !== $id || data_get($version, 'document.code') !== $this->code($document, $scope) || data_get($version, 'document.scope') !== $scope) {
            throw new WayoutApiException('La versione del documento legale non è disponibile.', 503, 'LEGAL_VERSION_UNAVAILABLE');
        }

        return $this->normalize($document, $locale ?? app()->getLocale(), ['name' => $current->title], $version, $scope);
    }

    private function code(string $document, string $scope = 'SITE'): string
    {
        if ($scope === 'SITE' && in_array($document, $this->keys(), true)) {
            return (string) config('legal.backend_codes.'.$document);
        }
        $document = strtoupper(str_replace('-', '_', $document));
        abort_unless(preg_match('/^[A-Z][A-Z0-9_-]{0,79}$/D', $document), 404);

        return $document;
    }

    private function assertScope(?string $scope): void
    {
        abort_unless($scope === null || in_array($scope, ['APP', 'SITE'], true), 404);
    }

    private function normalize(string $document, string $locale, array $data, array $version, string $scope): object
    {
        if (! is_string($version['content_html'] ?? null) || ! is_string($version['id'] ?? null)
            || ! isset($version['version'], $version['published_at'])) {
            throw new WayoutApiException('Il testo del documento legale non è disponibile.', 503, 'LEGAL_CONTENT_UNAVAILABLE');
        }

        return (object) [
            'document_key' => $document,
            'code' => $this->code($document, $scope),
            'scope' => $scope,
            'locale' => $locale,
            'version_id' => $version['id'],
            'version' => (string) $version['version'],
            'title' => $data['name'] ?? $version['label'] ?? $document,
            'description' => $data['name'] ?? '',
            'content_snapshot' => $version['content_html'],
            'content_hash' => hash('sha256', $version['content_html']),
            'content_format' => 'html',
            'published_at' => $version['published_at'],
        ];
    }
}
