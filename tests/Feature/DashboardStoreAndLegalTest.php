<?php

namespace Tests\Feature;

use App\Models\AdminUser;
use App\Support\AppStoreLinks;
use App\Support\DatabaseUuid;
use App\Support\LegalDocumentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class DashboardStoreAndLegalTest extends TestCase
{
    use RefreshDatabase;

    protected function usesDefaultLegalFixtures(): bool
    {
        return false;
    }

    private function adminSession(): array
    {
        $admin = AdminUser::create(['name' => 'Admin', 'email' => 'store-admin@example.com', 'password' => Hash::make('Testing-password-123'), 'is_active' => true, 'totp_confirmed_at' => now()]);

        return ['admin_authenticated' => true, 'admin_user_id' => $admin->id, 'admin_auth_version' => $admin->auth_version];
    }

    public function test_cookie_choices_remain_recorded_without_a_dashboard_tab(): void
    {
        $id = DatabaseUuid::new();
        $this->postJson('/cookie-consent', ['consent_id' => $id, 'consent_version' => 1, 'analytics' => true, 'marketing' => false])->assertNoContent();
        $this->assertDatabaseHas('cookie_consent_events', ['consent_id' => $id, 'analytics' => true, 'marketing' => false]);
        $this->withSession($this->adminSession())->get('/admin?tab=cookies')
            ->assertOk()->assertSee('id="admin-panel-settings"', false)
            ->assertDontSee('admin-panel-cookies')->assertDontSee($id);
    }

    public function test_admin_can_save_store_links_and_all_download_buttons_use_them(): void
    {
        Http::fake(['*/legal/documents' => Http::response(['data' => []])]);
        $links = ['apple' => 'https://apps.apple.com/it/app/wayout/id123', 'google' => 'https://play.google.com/store/apps/details?id=it.wayout'];
        $this->withSession($this->adminSession())->post(route('admin.app-store-links.update'), $links)
            ->assertRedirect(route('admin.dashboard', ['tab' => 'settings']))->assertSessionHas('admin_success');
        $this->assertSame($links, app(AppStoreLinks::class)->all());
        $this->assertDatabaseHas('admin_audit_events', ['action' => 'app_store_links.updated', 'target_type' => 'site_settings']);
        $this->get('/')->assertOk()->assertSee('href="'.$links['apple'].'"', false)->assertSee('href="'.$links['google'].'"', false);
    }

    public function test_clearing_a_store_link_disables_its_button_even_with_an_environment_default(): void
    {
        config()->set('services.app_stores.apple', 'https://apps.apple.com/previous');
        Http::fake(['*/legal/documents' => Http::response(['data' => []])]);
        $this->withSession($this->adminSession())->post(route('admin.app-store-links.update'), ['apple' => '', 'google' => ''])->assertRedirect();
        $this->assertSame(['apple' => null, 'google' => null], app(AppStoreLinks::class)->all());
        $response = $this->get('/')->assertOk()->assertDontSee('https://apps.apple.com/previous');
        $document = new \DOMDocument;
        @$document->loadHTML($response->getContent());
        $xpath = new \DOMXPath($document);
        $this->assertSame(4, $xpath->query('//button[@class="store-button"][@disabled]')->length);
    }

    public function test_store_links_require_authentication_and_valid_https_urls(): void
    {
        $this->post(route('admin.app-store-links.update'), ['apple' => 'https://apps.apple.com/example'])->assertRedirect(route('admin.login'));
        $this->withSession($this->adminSession())->post(route('admin.app-store-links.update'), ['apple' => 'javascript:alert(1)', 'google' => 'http://play.google.com/example'])
            ->assertSessionHasErrors(['apple', 'google']);
        $this->assertDatabaseCount('site_settings', 0);
    }

    private function document(string $scope): array
    {
        return ['code' => 'TERMS', 'scope' => $scope, 'name' => 'Termini '.$scope, 'current' => [
            'id' => $scope === 'APP' ? '8af55a1a-4019-4a13-9953-3157ee14e4b7' : '86fa730e-8c3f-4ded-99a6-aaa06ec03ab4',
            'version' => 1, 'label' => 'Versione iniziale', 'published_at' => '2026-11-13T15:45:29Z', 'content_html' => '<p>Contenuto '.$scope.'</p>',
        ]];
    }

    public function test_footer_has_separate_api_driven_site_and_app_links_without_duplicate_navigation(): void
    {
        Http::fake(['*/legal/documents' => Http::response(['data' => [$this->document('SITE'), $this->document('APP')]])]);
        $response = $this->get('/')->assertOk()->assertSee('Legale · Sito')->assertSee('Legale · App')
            ->assertSee('Termini SITE')->assertSee('Termini APP')->assertSee('cookie-preferences-open');
        $document = new \DOMDocument;
        @$document->loadHTML($response->getContent());
        $xpath = new \DOMXPath($document);
        $site = $xpath->query('//footer//nav[@data-legal-scope="SITE"]//a[contains(text(), "Termini SITE")]')->item(0);
        $app = $xpath->query('//footer//nav[@data-legal-scope="APP"]//a[contains(text(), "Termini APP")]')->item(0);
        $this->assertStringContainsString('scope=SITE', $site->getAttribute('href'));
        $this->assertStringContainsString('scope=APP', $app->getAttribute('href'));
        $this->assertSame(1, $xpath->query('//footer//a[contains(@href, "/piani")]')->length);
    }

    public function test_same_document_code_keeps_app_and_site_content_separate_in_pages_and_cache(): void
    {
        Http::fake([
            '*/legal/documents/TERMS?scope=SITE' => Http::response(['data' => $this->document('SITE')]),
            '*/legal/documents/TERMS?scope=APP' => Http::response(['data' => $this->document('APP')]),
            '*/legal/documents' => Http::response(['data' => [$this->document('SITE'), $this->document('APP')]]),
        ]);
        $service = app(LegalDocumentService::class);
        $this->assertSame('<p>Contenuto SITE</p>', $service->current('TERMS', scope: 'SITE')->content_snapshot);
        $this->assertSame('<p>Contenuto APP</p>', $service->current('TERMS', scope: 'APP')->content_snapshot);
        $this->get('/documenti-legali/TERMS?scope=APP')->assertOk()->assertSee('Contenuto APP')->assertDontSee('Contenuto SITE')
            ->assertSee('scope=APP', false);
        $this->get('/documenti-legali?scope=APP')->assertOk()->assertSee('data-legal-scope="APP"', false);
    }

    public function test_app_history_and_uuid_validation_preserve_scope(): void
    {
        $previous = '477e82d2-eb78-4bf7-9e15-2e5a91660fc7';
        Http::fake([
            '*/legal/documents/TERMS?scope=APP' => Http::response(['data' => $this->document('APP')]),
            '*/legal/documents/TERMS/versions?scope=APP' => Http::response(['data' => ['code' => 'TERMS', 'scope' => 'APP', 'versions' => [['id' => $previous, 'version' => 1, 'label' => 'Vecchi termini app', 'published_at' => '2026-10-01T12:00:00Z']]]]),
            '*/legal/versions/'.$previous => Http::response(['data' => ['id' => $previous, 'version' => 1, 'label' => 'Vecchi termini', 'published_at' => '2026-10-01T12:00:00Z', 'content_html' => '<p>Versione APP precedente</p>', 'document' => ['code' => 'TERMS', 'scope' => 'APP']]]),
            '*/legal/documents' => Http::response(['data' => [$this->document('APP')]]),
        ]);
        $this->get('/documenti-legali/TERMS/versioni?scope=APP')->assertOk()->assertSee('Vecchi termini app')->assertSee('scope=APP', false);
        $this->get('/documenti-legali/TERMS/versioni/'.$previous.'?scope=APP')->assertOk()->assertSee('Versione APP precedente');
    }
}
