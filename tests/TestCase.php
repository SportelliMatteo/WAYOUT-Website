<?php

namespace Tests;

use App\Support\DatabaseUuid;
use App\Support\LocalLegalDocumentArchive;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config()->set('services.wayout.base_url', 'https://staging-app.wayoutapp.test');
        config()->set('services.wayout.internal_secret', str_repeat('s', 48));
        config()->set('services.wayout.catalog_cache_seconds', 1);
        config()->set('services.firebase.project_id', 'wayout-test');
        config()->set('services.firebase.api_key', 'firebase-test-key');
        config()->set('services.firebase.auth_domain', 'wayout-test.firebaseapp.com');
        config()->set('services.firebase.app_id', '1:123:web:test');
        config()->set('services.recaptcha.enabled', false);
        config()->set('benefits.server_auth.key', 'test-benefit-key');
        config()->set('benefits.server_auth.secret', 'test-benefit-secret');
        if ($this->usesDefaultLegalFixtures()) {
            // A deterministic backend fixture for existing integration tests.
            Http::fake(function ($request) {
                $path = parse_url($request->url(), PHP_URL_PATH);
                if ($path === '/api/v1/legal/documents') {
                    parse_str(parse_url($request->url(), PHP_URL_QUERY) ?? '', $query);
                    $documents = [];
                    if (($query['scope'] ?? null) !== 'APP') {
                        foreach (config('legal.backend_codes') as $key => $code) {
                            $fixture = app(LocalLegalDocumentArchive::class)->current($key, app()->getLocale());
                            $documents[] = ['code' => $code, 'scope' => 'SITE', 'name' => $fixture->title, 'current' => ['id' => $fixture->version_id, 'version' => $fixture->version, 'published_at' => $fixture->published_at]];
                        }
                    }

                    return Http::response(['success' => true, 'data' => $documents]);
                }

                if (! preg_match('~^/api/v1/legal/documents/([A-Z_]+)$~', $path, $matches)) {
                    return null;
                }
                $key = array_search($matches[1], config('legal.backend_codes'), true);
                if ($key === false) {
                    return Http::response([], 404);
                }
                $locale = $request->header('Accept-Language')[0] ?? app()->getLocale();
                $fixture = app(LocalLegalDocumentArchive::class)->current($key, $locale);

                return Http::response(['success' => true, 'data' => [
                    'code' => $matches[1], 'scope' => 'SITE', 'name' => $fixture->title,
                    'current' => ['id' => $fixture->version_id, 'version' => $fixture->version, 'label' => $fixture->title,
                        'published_at' => $fixture->published_at, 'content_html' => $fixture->content_snapshot],
                ]]);
            });
        }
    }

    protected function usesDefaultLegalFixtures(): bool
    {
        return true;
    }

    protected function createVerifiedWaitlistEntry(string $email = 'member@example.com', int $position = 1): object
    {
        $id = DatabaseUuid::new();
        DB::table('waitlist_entries')->insert([
            'id' => $id,
            'benefit_id' => DatabaseUuid::new(),
            'email' => strtolower($email),
            'waitlist_position' => $position,
            'email_verified_at' => now(),
            'marketing_consent' => false,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return DB::table('waitlist_entries')->where('id', $id)->first();
    }

    /** @return array<string, string> */
    protected function benefitApiHeaders(string $method, string $path, string $body, ?string $nonce = null): array
    {
        $timestamp = (string) time();
        $nonce ??= 'nonce-'.bin2hex(random_bytes(12));
        $canonical = implode("\n", [
            $timestamp,
            $nonce,
            strtoupper($method),
            '/'.ltrim($path, '/'),
            hash('sha256', $body),
        ]);

        return [
            'X-Wayout-Key' => 'test-benefit-key',
            'X-Wayout-Timestamp' => $timestamp,
            'X-Wayout-Nonce' => $nonce,
            'X-Wayout-Signature' => hash_hmac('sha256', $canonical, 'test-benefit-secret'),
            'Content-Type' => 'application/json',
        ];
    }
}
