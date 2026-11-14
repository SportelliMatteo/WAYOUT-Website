<?php

namespace Tests\Feature;

use App\Support\ConsentAuditService;
use App\Support\LegalDocumentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class BackendLegalDocumentsTest extends TestCase
{
    use RefreshDatabase;

    private const CURRENT_ID = '86fa730e-8c3f-4ded-99a6-aaa06ec03ab4';

    private const PREVIOUS_ID = '477e82d2-eb78-4bf7-9e15-2e5a91660fc7';

    protected function usesDefaultLegalFixtures(): bool
    {
        return false;
    }

    private function currentDocument(string $code = 'COOKIES'): array
    {
        return ['code' => $code, 'scope' => 'SITE', 'name' => 'Documento backend', 'current' => [
            'id' => self::CURRENT_ID, 'version' => 2, 'label' => 'Pubblicazione corrente',
            'published_at' => '2026-11-13T20:57:36.999Z', 'content_html' => '<h2>Testo dal backend</h2><p>Contenuto pubblicato.</p>',
        ]];
    }

    public function test_current_page_uses_backend_html_and_site_scope_without_local_publication(): void
    {
        $count = DB::table('legal_document_versions')->count();
        Http::fake(['*/legal/documents/COOKIES*' => Http::response(['success' => true, 'data' => $this->currentDocument()])]);
        $this->get('/cookie-policy?lang=en')->assertOk()->assertSee('<h2>Testo dal backend</h2>', false)
            ->assertSee('legal')->assertSee('Version 2')
            ->assertSee('Published on:')->assertSee('13 November 2026')
            ->assertSee('datetime="2026-11-13T20:57:36.999Z"', false)
            ->assertDontSee('Last updated:')->assertDontSee('02 November 2026');
        Http::assertSent(fn ($request) => $request->url() === 'https://staging-app.wayoutapp.test/api/v1/legal/documents/COOKIES?scope=SITE'
            && $request->hasHeader('Accept-Language', 'en') && ! $request->hasHeader('Authorization') && ! $request->hasHeader('X-Wayout-Signature'));
        $this->assertSame($count, DB::table('legal_document_versions')->count());
    }

    public function test_publication_date_uses_backend_metadata_in_italian_and_rome_timezone(): void
    {
        config()->set('app.display_timezone', 'Europe/Rome');
        $document = $this->currentDocument();
        $document['current']['published_at'] = '2026-11-14T23:30:00Z';
        $document['current']['version'] = 7;
        Http::fake(['*/legal/documents/COOKIES*' => Http::response(['data' => $document])]);
        $this->get('/cookie-policy?lang=it')->assertOk()
            ->assertSee('Pubblicato il:')->assertSee('15 novembre 2026')->assertSee('Versione 7')
            ->assertDontSee('Ultimo aggiornamento:')->assertDontSee('02 novembre 2026');
    }

    public function test_published_index_excludes_app_documents(): void
    {
        Http::fake(['*/legal/documents?scope=SITE' => Http::response(['data' => [
            ['code' => 'COOKIES', 'name' => 'Cookie', 'scope' => 'SITE', 'current' => ['id' => self::CURRENT_ID]],
            ['code' => 'APP_TERMS', 'scope' => 'APP', 'current' => ['id' => self::PREVIOUS_ID]],
        ]])]);
        $documents = app(LegalDocumentService::class)->published();
        $this->assertCount(1, $documents);
        $this->assertSame('COOKIES', $documents[0]['code']);
    }

    public function test_history_reads_nested_versions_and_excludes_current_version(): void
    {
        Http::fake([
            '*/legal/documents/COOKIES?scope=SITE' => Http::response(['data' => $this->currentDocument()]),
            '*/legal/documents/COOKIES/versions?scope=SITE' => Http::response(['data' => [
                'code' => 'COOKIES', 'scope' => 'SITE', 'name' => 'Cookie', 'versions' => [
                    ['id' => self::CURRENT_ID, 'version' => 2, 'published_at' => '2026-11-13T20:57:36Z'],
                    ['id' => self::PREVIOUS_ID, 'version' => 1, 'label' => 'Prima pubblicazione', 'published_at' => '2026-10-01T12:00:00Z'],
                ],
            ]]),
        ]);
        $this->get(route('legal.history', ['document' => 'cookies']))->assertOk()
            ->assertSee('Prima pubblicazione')->assertSee(self::PREVIOUS_ID)->assertDontSee(self::CURRENT_ID);
    }

    public function test_previous_version_requires_membership_in_site_history_and_uses_uuid_api(): void
    {
        Http::fake([
            '*/legal/documents/COOKIES?scope=SITE' => Http::response(['data' => $this->currentDocument()]),
            '*/legal/documents/COOKIES/versions?scope=SITE' => Http::response(['data' => [
                'code' => 'COOKIES', 'scope' => 'SITE', 'versions' => [['id' => self::PREVIOUS_ID, 'version' => 1]],
            ]]),
            '*/legal/versions/'.self::PREVIOUS_ID => Http::response(['data' => [
                'id' => self::PREVIOUS_ID, 'version' => 1, 'label' => 'Versione precedente',
                'content_html' => '<p>Testo precedente dal backend</p>', 'published_at' => '2026-10-01T12:00:00Z',
                'document' => ['code' => 'COOKIES', 'scope' => 'SITE', 'name' => 'Cookie'],
            ]]),
        ]);
        $this->get(route('legal.version', ['document' => 'cookies', 'version' => self::PREVIOUS_ID]))
            ->assertOk()->assertSee('Testo precedente dal backend')->assertSee('Stai leggendo una versione precedente.')
            ->assertSee('Pubblicato il:')->assertSee('01 ottobre 2026')->assertSee('Versione 1')
            ->assertDontSee('13 novembre 2026');
        $this->get(route('legal.version', ['document' => 'cookies', 'version' => '98b09265-eaa3-4492-8639-053343bf4523']))->assertNotFound();
        Http::assertNotSent(fn ($request) => str_contains($request->url(), '98b09265-eaa3-4492-8639-053343bf4523'));
    }

    public function test_missing_or_app_only_document_never_falls_back_to_local_text(): void
    {
        Http::fake(['*/legal/documents/COOKIES*' => Http::response(['message' => 'Not published'], 404)]);
        $this->get('/cookie-policy')->assertStatus(503)->assertSee('I documenti legali non sono al momento disponibili.')
            ->assertDontSee('Versione 1.0');
    }

    public function test_app_scope_is_rejected(): void
    {
        $document = $this->currentDocument();
        $document['scope'] = 'APP';
        Http::fake(['*/legal/documents/COOKIES*' => Http::response(['data' => $document])]);
        $this->get('/cookie-policy')->assertStatus(503);
    }

    public function test_missing_content_html_is_rejected_without_local_fallback(): void
    {
        $document = $this->currentDocument();
        unset($document['current']['content_html']);
        Http::fake(['*/legal/documents/COOKIES*' => Http::response(['data' => $document])]);
        $this->get('/cookie-policy')->assertStatus(503);
    }

    public function test_new_published_site_document_can_be_viewed_without_a_local_template(): void
    {
        $document = $this->currentDocument('NEW_POLICY');
        Http::fake([
            '*/legal/documents' => Http::response(['data' => [$document]]),
            '*/legal/documents?scope=SITE' => Http::response(['data' => [$document]]),
            '*/legal/documents/NEW_POLICY?scope=SITE' => Http::response(['data' => $document]),
        ]);
        $this->get(route('legal.index'))->assertOk()->assertSee('NEW_POLICY')->assertSee('Documento backend');
        $this->get(route('legal.document', ['document' => 'NEW_POLICY']))->assertOk()->assertSee('Testo dal backend');
    }

    public function test_backend_codes_can_be_configured_for_existing_public_urls(): void
    {
        config()->set('legal.backend_codes.cookies', 'SITE_COOKIE_POLICY');
        Http::fake(['*/legal/documents/SITE_COOKIE_POLICY?scope=SITE' => Http::response(['data' => $this->currentDocument('SITE_COOKIE_POLICY')])]);
        $this->get('/cookie-policy')->assertOk()->assertSee('Testo dal backend');
    }

    public function test_consent_snapshot_references_backend_version_and_exact_html_hash(): void
    {
        Http::fake(['*/legal/documents/COOKIES*' => Http::response(['data' => $this->currentDocument()])]);
        $id = app(ConsentAuditService::class)->record(Request::create('/'), 'buyer@example.com', 'test', 'granted', 'test', ['cookies']);
        $event = DB::table('consent_events')->where('id', $id)->first();
        $this->assertSame('2', json_decode($event->document_versions, true)['cookies']);
        $this->assertSame(hash('sha256', $this->currentDocument()['current']['content_html']), json_decode($event->document_hashes, true)['cookies']);
        $this->assertStringContainsString(self::CURRENT_ID, json_decode($event->document_urls, true)['cookies']);
    }

    public function test_contact_page_shows_unavailability_instead_of_old_acceptance_html(): void
    {
        Http::fake(['*/legal/documents/CONTACT_ACCEPTANCE*' => Http::response([], 404)]);
        $this->get('/contatti')->assertOk()->assertSee('I documenti legali non sono al momento disponibili.')
            ->assertSee('type="submit" disabled', false);
    }
}
