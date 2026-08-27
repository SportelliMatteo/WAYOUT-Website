<?php

namespace Tests\Feature;

use App\Models\AdminUser;
use App\Support\CheckoutFeatures;
use App\Support\DatabaseUuid;
use App\Support\LegalDocumentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;

class AdminDashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_dashboard_requires_login(): void
    {
        $response = $this->get(route('admin.dashboard'));

        $response->assertRedirect(route('admin.login'));
    }

    public function test_configured_credentials_bootstrap_first_equal_admin_and_require_otp_setup(): void
    {
        config()->set('admin.email', 'admin@wayout.test');
        config()->set('admin.password', 'secret-password');

        $response = $this->post(route('admin.authenticate'), [
            'email' => 'admin@wayout.test',
            'password' => 'secret-password',
        ]);

        $response->assertRedirect(route('admin.otp.setup'));
        $this->assertFalse(session()->has('admin_authenticated'));
        $this->assertDatabaseHas('admin_users', [
            'email' => 'admin@wayout.test',
        ]);
    }

    public function test_admin_dashboard_shows_waitlist_purchase_and_revenue_totals(): void
    {
        $this->seedDashboardData();

        $response = $this->withSession($this->adminSession())
            ->get(route('admin.dashboard'));

        $response->assertOk()
            ->assertSee('Waitlist e ordini')
            ->assertSee('buyer@example.com')
            ->assertSee('Founder Creator 12M')
            ->assertSee('Marketing')
            ->assertSee('88,00€')
            ->assertSee('29,00€')
            ->assertSee('59,00€')
            ->assertSee('Capienze Founder')
            ->assertSee('Utenti Founder Join 12M')
            ->assertSee('Utenti Founder Creator 12M')
            ->assertSee('Verificata')
            ->assertSee('In attesa di verifica');
    }

    public function test_admin_dashboard_sections_and_legal_categories_are_accessible_as_tabs(): void
    {
        $response = $this->withSession($this->adminSession())
            ->get(route('admin.dashboard', [
                'tab' => 'legal',
                'legal_group' => 'consent_texts',
            ]));

        $response->assertOk()
            ->assertSee('role="tablist"', false)
            ->assertSee('data-admin-tab="legal"', false)
            ->assertSee('data-admin-panel="legal" class=""', false)
            ->assertSee('data-legal-tab="consent_texts"', false)
            ->assertSee('data-legal-panel="consent_texts"', false)
            ->assertSee('Testi delle checkbox');
    }

    public function test_admin_can_update_founder_capacities(): void
    {
        $response = $this->withSession($this->adminSession())
            ->post(route('admin.settings.update'), [
                'waitlist_capacity' => 2500,
                'join_capacity' => 700,
                'creator_capacity' => 220,
            ]);

        $response->assertRedirect()
            ->assertSessionHas('admin_success', 'Dati aggiornati.');

        $this->assertDatabaseHas('founder_settings', [
            'key' => 'waitlist_capacity',
            'value' => 2500,
        ]);

        $this->assertDatabaseHas('founder_settings', [
            'key' => 'join_capacity',
            'value' => 700,
        ]);

        $this->assertDatabaseHas('founder_settings', [
            'key' => 'creator_capacity',
            'value' => 220,
        ]);

        $audit = DB::table('admin_audit_events')
            ->where('action', 'founder_capacities.updated')
            ->first();

        $this->assertNotNull($audit);
        $this->assertSame('admin@example.com', $audit->actor_email);
        $this->assertSame(2500, json_decode($audit->new_values, true)['waitlist_capacity']);
    }

    public function test_admin_can_enable_legal_entity_invoicing(): void
    {
        $session = $this->adminSession();
        $response = $this->withSession($session)
            ->post(route('admin.checkout-settings.update'), [
                'legal_entity_invoice_enabled' => true,
            ]);

        $response->assertRedirect(route('admin.dashboard', ['tab' => 'settings']))
            ->assertSessionHas('admin_success', 'Opzioni di fatturazione aggiornate.');

        $this->assertDatabaseHas('founder_settings', [
            'key' => CheckoutFeatures::LEGAL_ENTITY_INVOICE_KEY,
            'value' => 1,
        ]);
        $this->assertDatabaseHas('admin_audit_events', [
            'actor_email' => 'admin@example.com',
            'action' => 'checkout_settings.updated',
            'target_type' => 'checkout_settings',
        ]);

        $this->withSession($session)
            ->get(route('admin.dashboard', ['tab' => 'settings']))
            ->assertOk()
            ->assertSee('Fattura a persona giuridica')
            ->assertSee('<option value="1" selected>Abilitato</option>', false);
    }

    public function test_admin_can_publish_a_new_legal_version_used_by_the_public_page(): void
    {
        $response = $this->withSession($this->adminSession())
            ->post(route('admin.legal-documents.publish', ['document' => 'privacy']), [
                'document_key' => 'privacy',
                'locale' => 'it',
                'version' => '2026-07-15',
                'title' => 'Privacy policy aggiornata',
                'description' => 'Nuova descrizione privacy.',
                'content_html' => '<div><h2>Nuovo testo privacy</h2><p>Contenuto pubblicato dal database.</p><script>alert(1)</script></div>',
            ]);

        $response->assertRedirect(route('admin.dashboard', [
            'lang' => 'it',
            'tab' => 'legal',
            'legal_group' => 'policies',
        ]).'#legal-documents')
            ->assertSessionHas('admin_success');

        $version = DB::table('legal_document_versions')
            ->where('document_key', 'privacy')
            ->where('locale', 'it')
            ->where('version', '2026-07-15')
            ->first();

        $this->assertNotNull($version);
        $this->assertSame('html', $version->content_format);
        $this->assertStringNotContainsString('<script', $version->content_snapshot);
        $this->assertDatabaseHas('legal_documents', [
            'document_key' => 'privacy',
            'locale' => 'it',
            'current_version_id' => $version->id,
        ]);

        $this->assertDatabaseHas('admin_audit_events', [
            'actor_email' => 'admin@example.com',
            'action' => 'legal_document.published',
            'target_type' => 'legal_document',
            'target_id' => $version->id,
        ]);

        $this->get(route('legal.privacy'))
            ->assertOk()
            ->assertSee('Privacy policy aggiornata')
            ->assertSee('Nuovo testo privacy')
            ->assertSee('Versione 2026-07-15')
            ->assertDontSee('alert(1)');
    }

    public function test_legal_document_publication_requires_admin_authentication(): void
    {
        $this->post(route('admin.legal-documents.publish', ['document' => 'privacy']), [])
            ->assertRedirect(route('admin.login'));
    }

    public function test_admin_can_publish_multiple_legal_revisions_on_the_same_date(): void
    {
        $date = now()->toDateString();
        $session = $this->adminSession();
        $existingRevisions = DB::table('legal_document_versions')
            ->where('document_key', 'privacy')
            ->where('locale', 'it')
            ->where(fn ($query) => $query
                ->where('version', $date)
                ->orWhere('version', 'like', $date.'.%'))
            ->count();
        $payload = [
            'document_key' => 'privacy',
            'locale' => 'it',
            'version' => $date,
            'title' => 'Privacy aggiornata oggi',
            'description' => 'Descrizione aggiornata oggi.',
            'content_html' => '<div><h2>Prima revisione</h2></div>',
        ];

        $this->withSession($session)
            ->post(route('admin.legal-documents.publish', ['document' => 'privacy']), $payload)
            ->assertSessionHas('admin_success');

        $this->withSession($session)
            ->post(route('admin.legal-documents.publish', ['document' => 'privacy']), [
                ...$payload,
                'content_html' => '<div><h2>Seconda revisione</h2></div>',
            ])->assertSessionHas('admin_success');

        $versions = DB::table('legal_document_versions')
            ->where('document_key', 'privacy')
            ->where('locale', 'it')
            ->where(fn ($query) => $query
                ->where('version', $date)
                ->orWhere('version', 'like', $date.'.%'))
            ->orderBy('id')
            ->pluck('version');

        $this->assertCount($existingRevisions + 2, $versions);
        $current = app(LegalDocumentService::class)->current('privacy', 'it');
        $this->assertTrue($versions->contains($current->version));
        $this->assertStringContainsString('Seconda revisione', $current->content_snapshot);
    }

    public function test_admin_dashboard_can_filter_waitlist_buyers(): void
    {
        $this->seedDashboardData();

        $response = $this->withSession($this->adminSession())
            ->get(route('admin.dashboard', ['status' => 'buyers']));

        $response->assertOk()
            ->assertSee('buyer@example.com')
            ->assertDontSee('lead@example.com');
    }

    public function test_admin_dashboard_can_filter_users_eligible_for_free_waitlist_benefit(): void
    {
        $this->seedDashboardData();

        $response = $this->withSession($this->adminSession())
            ->get(route('admin.dashboard', [
                'tab' => 'users',
                'status' => 'benefit_eligible',
            ]));

        $response->assertOk()
            ->assertSee('eligible@example.com')
            ->assertSee('60 giorni');
        $this->assertSame(
            ['eligible@example.com'],
            $response->viewData('waitlistEntries')->pluck('email')->all()
        );
    }

    public function test_admin_dashboard_displays_and_searches_prelaunch_identifiers(): void
    {
        $this->seedDashboardData();
        $session = $this->adminSession();

        $this->withSession($session)
            ->get(route('admin.dashboard', ['tab' => 'users', 'q' => '11111111']))
            ->assertOk()
            ->assertSee('buyer@example.com')
            ->assertSee('11111111-1111-4111-8111-111111111111')
            ->assertSee('Verificata')
            ->assertSee('1 risultati');

        $this->withSession($session)
            ->get(route('admin.dashboard', ['tab' => 'users', 'q' => 'buyer@example.com']))
            ->assertOk()
            ->assertSee('buyer@example.com')
            ->assertSee('Posizione')
            ->assertSee('1 risultati');

        $this->withSession($session)
            ->get(route('admin.dashboard', ['tab' => 'users', 'q' => 'lead@example.com']))
            ->assertOk()
            ->assertSee('lead@example.com')
            ->assertSee('In attesa di verifica')
            ->assertSee('1 risultati');
    }

    public function test_admin_dashboard_displays_utc_consent_timestamps_in_rome_timezone(): void
    {
        DB::table('consent_events')->insert([
            'id' => DatabaseUuid::new(),
            'subject_email' => 'timezone@example.com',
            'consent_type' => 'marketing',
            'action' => 'granted',
            'source' => 'test',
            'document_versions' => json_encode([]),
            'document_hashes' => json_encode([]),
            'document_urls' => json_encode([]),
            'locale' => 'it',
            'occurred_at' => '2026-07-15 20:00:00',
            'created_at' => '2026-07-15 20:00:00',
        ]);

        $response = $this->withSession($this->adminSession())
            ->get(route('admin.dashboard', ['tab' => 'consents']));

        $response->assertOk()
            ->assertSee('15/07/2026 22:00');
    }

    public function test_email_log_channel_uses_rome_timezone(): void
    {
        $this->assertSame(
            'Europe/Rome',
            Log::channel('email')->getLogger()->getTimezone()->getName()
        );
    }

    private function adminSession(): array
    {
        $admin = AdminUser::query()->create([
            'name' => 'Test Admin',
            'email' => 'admin@example.com',
            'password' => Hash::make('Testing-password-123'),
            'is_active' => true,
            'totp_confirmed_at' => now(),
        ]);

        return [
            'admin_authenticated' => true,
            'admin_user_id' => $admin->id,
            'admin_auth_version' => $admin->auth_version,
        ];
    }

    private function seedDashboardData(): void
    {
        $buyerId = DatabaseUuid::new();
        DB::table('waitlist_entries')->insert([
            [
                'id' => $buyerId,
                'benefit_id' => '11111111-1111-4111-8111-111111111111',
                'email' => 'buyer@example.com',
                'waitlist_position' => 1,
                'email_verified_at' => now()->subDay(),
                'marketing_consent' => true,
                'created_at' => now()->subDays(2),
                'updated_at' => now()->subDays(2),
            ],
            [
                'id' => DatabaseUuid::new(),
                'benefit_id' => DatabaseUuid::new(),
                'email' => 'eligible@example.com',
                'waitlist_position' => 2,
                'email_verified_at' => now()->subHours(12),
                'marketing_consent' => false,
                'created_at' => now()->subDay(),
                'updated_at' => now()->subDay(),
            ],
            [
                'id' => DatabaseUuid::new(),
                'benefit_id' => DatabaseUuid::new(),
                'email' => 'lead@example.com',
                'waitlist_position' => null,
                'email_verified_at' => null,
                'marketing_consent' => false,
                'created_at' => now()->subDay(),
                'updated_at' => now()->subDay(),
            ],
        ]);

        DB::table('purchases')->insert([
            [
                'id' => DatabaseUuid::new(),
                'waitlist_entry_id' => $buyerId,
                'email' => 'buyer@example.com',
                'plan' => 'join',
                'amount' => 2900,
                'currency' => 'eur',
                'stripe_session_id' => 'direct_join',
                'status' => 'succeeded',
                'created_at' => now()->subHours(3),
                'updated_at' => now()->subHours(3),
            ],
            [
                'id' => DatabaseUuid::new(),
                'waitlist_entry_id' => $buyerId,
                'email' => 'buyer@example.com',
                'plan' => 'creator',
                'amount' => 5900,
                'currency' => 'eur',
                'stripe_session_id' => 'direct_creator',
                'status' => 'succeeded',
                'created_at' => now()->subHours(2),
                'updated_at' => now()->subHours(2),
            ],
        ]);
    }
}
