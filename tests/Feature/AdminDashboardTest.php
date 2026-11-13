<?php

namespace Tests\Feature;

use App\Models\AdminUser;
use App\Support\CheckoutFeatures;
use App\Support\DatabaseUuid;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;

class AdminDashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_activity_pagination_exposes_only_its_table_for_partial_replacement(): void
    {
        for ($i = 1; $i <= 7; $i++) {
            DB::table('admin_audit_events')->insert([
                'id' => DatabaseUuid::new(), 'actor_name' => 'Operatore', 'actor_email' => 'admin@example.com',
                'action' => 'activity_'.$i, 'target_type' => 'settings', 'occurred_at' => now()->subMinutes($i),
            ]);
        }
        $response = $this->withSession($this->adminSession())->get(route('admin.dashboard', [
            'tab' => 'settings', 'audit_per_page' => 5, 'audit_page' => 2,
        ]))->assertOk();
        $document = new \DOMDocument;
        @$document->loadHTML($response->getContent());
        $xpath = new \DOMXPath($document);
        $footer = $xpath->query('//*[@id="pagination-audit_page"]')->item(0);
        $this->assertNotNull($footer);
        $this->assertSame('audit_page', $footer->getAttribute('data-admin-pagination'));
        $this->assertSame('settings', $footer->getAttribute('data-pagination-tab'));
        $table = $xpath->query('preceding-sibling::*[1]', $footer)->item(0);
        $this->assertSame(1, $table->getElementsByTagName('table')->length);
        $this->assertSame(0, $table->getElementsByTagName('form')->length);
        $this->assertStringContainsString('activity_6', $table->textContent);
        $this->assertStringNotContainsString('activity_1', $table->textContent);
        $previous = $xpath->query('.//a[@rel="prev"]', $footer)->item(0);
        $this->assertSame('pagination-audit_page', parse_url($previous->getAttribute('href'), PHP_URL_FRAGMENT));
    }

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

    public function test_admin_dashboard_shows_retention_schedule_candidates_and_history(): void
    {
        DB::table('waitlist_entries')->insert([
            'id' => DatabaseUuid::new(),
            'email' => 'expired-unverified@example.com',
            'email_verified_at' => null,
            'marketing_consent' => false,
            'created_at' => now()->subDays(31),
            'updated_at' => now()->subDays(31),
        ]);
        DB::table('data_retention_runs')->insert([
            'id' => DatabaseUuid::new(),
            'trigger' => 'scheduled',
            'dry_run' => false,
            'status' => 'completed',
            'results' => json_encode([
                'email_verifications' => ['matched' => 2, 'affected' => 2, 'cutoff' => now()->toISOString()],
                'unverified_waitlist' => ['matched' => 1, 'affected' => 1, 'cutoff' => now()->subDays(30)->toISOString()],
                'admin_audit_events' => ['matched' => 3, 'affected' => 3, 'cutoff' => now()->subYear()->toISOString()],
            ]),
            'started_at' => now()->subHour(),
            'completed_at' => now()->subHour()->addSecond(),
            'created_at' => now()->subHour(),
        ]);

        $this->withSession($this->adminSession())
            ->get(route('admin.dashboard', ['tab' => 'settings']))
            ->assertOk()
            ->assertSee('Retention automatica dei dati')
            ->assertSee('Ogni ora al minuto 20')
            ->assertSee('Waitlist non verificata')
            ->assertSee('1')
            ->assertSee('Storico delle esecuzioni')
            ->assertSee('Pianificato')
            ->assertSee('Completato');
    }

    public function test_email_log_channel_uses_rome_timezone(): void
    {
        $this->assertSame(
            'Europe/Rome',
            Log::channel('email')->getLogger()->getTimezone()->getName()
        );
    }

    public function test_dashboard_contains_only_settings_and_does_not_fetch_sales_or_legal_catalogs(): void
    {
        Http::fake();
        $this->withSession($this->adminSession())->get(route('admin.dashboard', ['tab' => 'orders']))
            ->assertOk()->assertSee('Configurazioni sito')->assertSee('id="admin-panel-settings"', false)
            ->assertDontSee('admin-panel-orders')->assertDontSee('admin-panel-overview')
            ->assertDontSee('admin-panel-users')->assertDontSee('admin-panel-withdrawals')
            ->assertDontSee('admin-panel-consents')->assertDontSee('admin-panel-legal')
            ->assertDontSee('admin.legal-documents.publish');
        Http::assertNothingSent();
    }

    public function test_removed_admin_payment_and_publication_endpoints_return_not_found(): void
    {
        $this->withSession($this->adminSession());
        foreach (['/admin/withdrawals/example/refund', '/admin/purchases/example/invoice/retry', '/admin/purchases/example/invoice/sync', '/admin/legal-documents/privacy'] as $path) {
            $this->post($path)->assertNotFound();
        }
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
}
