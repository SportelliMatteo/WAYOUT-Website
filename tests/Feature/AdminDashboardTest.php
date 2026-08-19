<?php

namespace Tests\Feature;

use App\Models\AdminUser;
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
            ->assertSee('Verificato')
            ->assertSee('Non verificato');
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
            ])->assertSessionHas('admin_success', fn (string $message) => str_contains($message, $date.'.3'));

        $this->assertDatabaseHas('legal_document_versions', [
            'document_key' => 'privacy',
            'locale' => 'it',
            'version' => $date.'.2',
        ]);
        $this->assertDatabaseHas('legal_document_versions', [
            'document_key' => 'privacy',
            'locale' => 'it',
            'version' => $date.'.3',
        ]);
        $this->assertSame(
            $date.'.3',
            app(LegalDocumentService::class)->current('privacy', 'it')->version,
        );
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

    public function test_admin_dashboard_displays_new_profile_fields_and_searches_people(): void
    {
        $this->seedDashboardData();
        $session = $this->adminSession();

        $this->withSession($session)
            ->get(route('admin.dashboard', ['tab' => 'users', 'q' => 'nightowl']))
            ->assertOk()
            ->assertSee('buyer@example.com')
            ->assertSee('nightowl')
            ->assertSee('Altro')
            ->assertSee('1 risultati');

        $this->withSession($session)
            ->get(route('admin.dashboard', ['tab' => 'users', 'q' => '+393331234567']))
            ->assertOk()
            ->assertSee('buyer@example.com')
            ->assertSee('nightowl')
            ->assertSee('1 risultati');

        $this->withSession($session)
            ->get(route('admin.dashboard', ['tab' => 'users', 'q' => 'Giulia']))
            ->assertOk()
            ->assertSee('lead@example.com')
            ->assertSee('citylights')
            ->assertSee('Femmina')
            ->assertSee('1 risultati');
    }

    public function test_admin_dashboard_displays_utc_consent_timestamps_in_rome_timezone(): void
    {
        DB::table('consent_events')->insert([
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
        DB::table('waitlist_entries')->insert([
            [
                'email' => 'buyer@example.com',
                'first_name' => 'Mario',
                'last_name' => 'Rossi',
                'nickname' => 'nightowl',
                'gender' => 'OTHER',
                'phone_prefix' => '+39',
                'phone_number' => '3331234567',
                'phone_verified_at' => now()->subDay(),
                'marketing_consent' => true,
                'offer_shown' => true,
                'created_at' => now()->subDays(2),
                'updated_at' => now()->subDays(2),
            ],
            [
                'email' => 'lead@example.com',
                'first_name' => 'Giulia',
                'last_name' => 'Bianchi',
                'nickname' => 'citylights',
                'gender' => 'FEMALE',
                'phone_prefix' => '+39',
                'phone_number' => '3337654321',
                'phone_verified_at' => null,
                'marketing_consent' => false,
                'offer_shown' => true,
                'created_at' => now()->subDay(),
                'updated_at' => now()->subDay(),
            ],
        ]);

        DB::table('purchases')->insert([
            [
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
