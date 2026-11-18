<?php

namespace Tests\Feature;

use App\Models\AdminUser;
use App\Support\CheckoutFeatures;
use App\Support\DatabaseUuid;
use App\Support\SiteVisibility;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class PlansAvailabilityTest extends TestCase
{
    use RefreshDatabase;

    private function enabled(bool $enabled): void
    {
        DB::table('founder_settings')->where('key', CheckoutFeatures::PLANS_ENABLED_KEY)->update(['value' => $enabled ? 1 : 0]);
    }

    private function adminSession(): array
    {
        $admin = AdminUser::create([
            'name' => 'Admin', 'email' => 'plans-admin@example.com', 'password' => Hash::make('Testing-password-123'),
            'is_active' => true, 'totp_confirmed_at' => now(),
        ]);

        return ['admin_authenticated' => true, 'admin_user_id' => $admin->id, 'admin_auth_version' => $admin->auth_version];
    }

    public function test_admin_can_disable_and_enable_the_flow_with_an_audit_record(): void
    {
        $this->withSession($this->adminSession())->get('/admin')->assertOk()->assertSee('Disponibilità dei piani');
        $this->post(route('admin.plans-availability.update'), ['plans_enabled' => '0'])->assertRedirect(route('admin.dashboard', ['tab' => 'settings']));
        $this->assertFalse(app(CheckoutFeatures::class)->plansEnabled());
        $this->assertDatabaseHas('admin_audit_events', ['action' => 'plans.availability_updated', 'target_type' => 'checkout_settings']);
        $this->getJson('/subscribe/plans')->assertNotFound()->assertJsonPath('code', 'PLANS_DISABLED');
        $this->post(route('admin.plans-availability.update'), ['plans_enabled' => '1'])->assertRedirect();
        $this->assertTrue(app(CheckoutFeatures::class)->plansEnabled());
        $this->get('/')->assertOk()->assertSee('href="'.route('subscribe').'"', false);
    }

    public function test_disabled_flow_blocks_direct_requests_before_any_backend_checkout_or_catalog_call(): void
    {
        $this->enabled(false);
        foreach (['/piani', '/subscribe', '/subscribe/plans'] as $path) {
            $this->getJson($path)->assertNotFound()->assertJsonPath('code', 'PLANS_DISABLED')
                ->assertHeader('Cache-Control', 'no-store, private');
        }
        foreach (['/subscribe/checkout', '/purchase/confirmation'] as $path) {
            $this->postJson($path, ['plan' => 'price_test'])->assertNotFound()->assertJsonPath('code', 'PLANS_DISABLED');
        }
        foreach (['checkout.success', 'checkout.status', 'checkout.cancel'] as $route) {
            $url = URL::temporarySignedRoute($route, now()->addMinutes(10), ['purchase' => DatabaseUuid::new()]);
            $this->getJson($url)->assertNotFound()->assertJsonPath('code', 'PLANS_DISABLED');
        }
        Http::assertNothingSent();
        $this->assertDatabaseCount('purchases', 0);
    }

    public function test_disabled_flow_hides_all_public_purchase_links_and_returns_an_html_404(): void
    {
        Http::fake(['*/faqs/grouped?type=SITE' => Http::response(['success' => true, 'data' => []])]);
        $this->enabled(false);
        foreach (['/', '/chi-siamo', '/faq', '/contatti'] as $path) {
            $response = $this->get($path)->assertOk();
            $document = new \DOMDocument;
            @$document->loadHTML($response->getContent());
            $links = (new \DOMXPath($document))->query('//a[contains(@href, "/piani") or contains(@href, "/subscribe")]');
            $this->assertSame(0, $links->length, $path);
        }
        $this->get('/piani')->assertNotFound()->assertSee('Pagina non disponibile')
            ->assertHeader('Cache-Control', 'no-store, private');
        Http::assertNotSent(fn (Request $request) => str_contains($request->url(), '/promo-packages') || str_ends_with($request->url(), '/api/v1/plans'));
    }

    public function test_disabling_after_opening_the_catalog_blocks_subsequent_checkout_submission(): void
    {
        Http::fake(['*/internal/promo-packages' => Http::response(['data' => []])]);
        $this->get('/piani')->assertOk();
        $this->enabled(false);
        $this->postJson('/subscribe/checkout', ['plan' => 'price_test'])->assertNotFound()->assertJsonPath('code', 'PLANS_DISABLED');
        Http::assertNotSent(fn (Request $request) => str_contains($request->url(), '/internal/checkout-sessions'));
    }

    public function test_admin_preview_cannot_bypass_the_disabled_purchase_flow(): void
    {
        $session = $this->adminSession();
        DB::table('founder_settings')->where('key', SiteVisibility::SETTING_KEY)->update(['value' => 1]);
        $this->enabled(false);
        $session['admin_site_preview'] = [
            'admin_id' => $session['admin_user_id'], 'auth_version' => (string) $session['admin_auth_version'],
            'expires_at' => now()->addHour()->timestamp,
        ];
        $this->withSession($session)->getJson('/subscribe/plans')->assertNotFound()->assertJsonPath('code', 'PLANS_DISABLED');
        Http::assertNothingSent();
    }

    public function test_absent_setting_disables_the_flow_and_admin_change_requires_authentication_and_a_boolean(): void
    {
        DB::table('founder_settings')->where('key', CheckoutFeatures::PLANS_ENABLED_KEY)->delete();
        $this->getJson('/subscribe/plans')->assertNotFound();
        $this->post(route('admin.plans-availability.update'), ['plans_enabled' => 1])->assertRedirect(route('admin.login'));
        $this->withSession($this->adminSession())->post(route('admin.plans-availability.update'), ['plans_enabled' => 'invalid'])
            ->assertSessionHasErrors('plans_enabled');
        $this->assertFalse(app(CheckoutFeatures::class)->plansEnabled());
    }

    public function test_support_and_legal_pages_remain_available_when_sales_are_disabled(): void
    {
        $this->enabled(false);
        foreach (['/cancellazione-account', '/disdetta-abbonamento', '/recedere-dal-contratto', '/privacy-policy'] as $path) {
            $this->get($path)->assertOk();
        }
    }
}
