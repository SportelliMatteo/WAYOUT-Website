<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Tests\TestCase;

class AnalyticsConsentTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_layout_contains_consent_manager_configuration_without_server_side_trackers(): void
    {
        config()->set('analytics.enabled', true);
        config()->set('analytics.gtm_id', 'GTM-TEST123');
        config()->set('analytics.ga4_id', 'G-TEST123');
        config()->set('analytics.meta_pixel_id', '1234567890');

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('id="wayout-analytics-config"', false)
            ->assertSee('data-gtm-id="GTM-TEST123"', false)
            ->assertSee('id="cookie-consent-banner"', false)
            ->assertSee('id="cookie-consent-accept"', false)
            ->assertSee('id="cookie-consent-reject"', false)
            ->assertSee('id="cookie-consent-customize"', false)
            ->assertSee('id="cookie-preferences-open"', false)
            ->assertDontSee('<script src="https://www.googletagmanager.com', false)
            ->assertDontSee('<script src="https://connect.facebook.net', false);
    }

    public function test_unconfirmed_checkout_return_does_not_claim_success_or_emit_purchase(): void
    {
        config()->set('services.stripe.secret', null);

        $this->get(route('checkout.success', ['session_id' => 'cs_unknown']))
            ->assertOk()
            ->assertSee('Stiamo verificando il pagamento.')
            ->assertDontSee('Il tuo Founder Pass è confermato.')
            ->assertDontSee('data-analytics-page-event="purchase"', false);
    }

    public function test_cookie_preference_is_recorded_without_raw_network_identifiers(): void
    {
        $consentId = (string) Str::uuid();

        $this->withServerVariables([
            'REMOTE_ADDR' => '203.0.113.42',
            'HTTP_USER_AGENT' => 'WAYOUT consent test browser',
        ])->postJson(route('cookie-consent.store'), [
            'consent_id' => $consentId,
            'consent_version' => 1,
            'analytics' => true,
            'marketing' => false,
        ])->assertNoContent();

        $event = DB::table('cookie_consent_events')->where('consent_id', $consentId)->first();

        $this->assertNotNull($event);
        $this->assertTrue((bool) $event->analytics);
        $this->assertFalse((bool) $event->marketing);
        $this->assertSame(64, strlen($event->ip_hash));
        $this->assertSame(64, strlen($event->user_agent_hash));
        $this->assertNotSame('203.0.113.42', $event->ip_hash);
        $this->assertNotSame('WAYOUT consent test browser', $event->user_agent_hash);
    }
}
