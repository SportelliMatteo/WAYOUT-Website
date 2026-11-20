<?php

namespace Tests\Feature;

use App\Support\CustomerAccount;
use App\Support\DatabaseUuid;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\URL;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class CustomerAccountTest extends TestCase
{
    use RefreshDatabase;

    private function profile(): array
    {
        return ['id' => '32131864-6d52-4b97-919d-99d4f2abc845', 'first_name' => 'Ada', 'last_name' => 'Rossi', 'email' => 'ada@example.com', 'mobile_number' => '+393331234567', 'date_of_birth' => '1990-01-01', 'gender' => 'FEMALE'];
    }

    private function sessionData(): array
    {
        return ['customer' => ['token' => Crypt::encryptString('private-backend-token'), 'expires_at' => now()->addHour()->timestamp, 'profile' => $this->profile()]];
    }

    private function backend(?array $subscription = null, int $status = 200): void
    {
        Http::fake([
            '*/api/v1/users/me' => Http::response(['data' => $this->profile()], $status),
            '*/api/v1/internal/users/*/subscription' => Http::response(['data' => [
                'has_active_entitlement' => $subscription !== null, 'subscription' => $subscription, 'offer' => $subscription['offer'] ?? null,
            ]]),
            '*/api/v1/me/subscription/history' => Http::response(['data' => []]),
            '*/api/v1/internal/promo-packages' => Http::response(['data' => []]),
        ]);
    }

    public function test_guests_cannot_access_account_or_submit_checkout_even_with_a_firebase_token(): void
    {
        $this->get(route('customer.dashboard'))->assertRedirect(route('customer.login'));
        $this->postJson(route('subscribe.checkout'), ['firebase_token' => 'some-token', 'plan' => 'price_test'])
            ->assertUnauthorized()->assertJsonPath('redirectUrl', route('customer.login'));
        Http::assertNothingSent();
    }

    public function test_login_page_has_phone_otp_and_private_cache_headers(): void
    {
        $this->get(route('customer.login'))->assertOk()->assertSee('payment-phone-send')->assertSee('payment-phone-verify')
            ->assertHeader('Cache-Control', 'no-store, private')->assertHeader('X-Robots-Tag', 'noindex, nofollow, noarchive');
    }

    public function test_verified_existing_user_gets_an_encrypted_session_and_lands_in_the_account(): void
    {
        Http::fake([
            '*/api/auth/verify-firebase-token' => Http::response(['data' => ['access_token' => 'private-backend-token']]),
            '*/api/v1/users/me' => Http::response(['data' => $this->profile()]),
        ]);
        $response = $this->post(route('customer.authenticate'), ['firebase_token' => 'valid-firebase-token']);
        $response->assertRedirect(route('customer.dashboard'))->assertSessionHas('customer.profile.id', $this->profile()['id']);
        $this->assertSame('private-backend-token', Crypt::decryptString(session('customer.token')));
        $this->assertNotSame('private-backend-token', session('customer.token'));
    }

    public function test_new_number_cannot_sign_in_or_create_a_profile_on_the_website(): void
    {
        Http::fake([
            '*/api/auth/verify-firebase-token' => Http::response(['data' => []], 200, ['X-Temp-Token' => 'temporary']),
        ]);
        $this->withSession(['customer_registration' => ['firebase_token' => 'stale-token']])
            ->post(route('customer.authenticate'), ['firebase_token' => 'valid-token'])
            ->assertRedirect(route('customer.login'))->assertSessionHasErrors(['login' => __('account.app_account_required')])
            ->assertSessionMissing('customer')->assertSessionMissing('customer_registration');
        Http::assertNotSent(fn ($request) => str_ends_with($request->url(), '/create-profile'));
        $this->get('/registrati')->assertNotFound();
        $this->post('/registrati', $this->profile())->assertNotFound();
        $this->get(route('customer.dashboard'))->assertRedirect(route('customer.login'));
    }

    public function test_unknown_user_backend_rejection_explains_that_registration_is_required_in_the_app(): void
    {
        Http::fake(['*/api/auth/verify-firebase-token' => Http::response(['code' => 'USER_NOT_FOUND'], 404)]);
        $this->post(route('customer.authenticate'), ['firebase_token' => 'valid-token'])
            ->assertRedirect(route('customer.login'))->assertSessionHasErrors(['login' => __('account.app_account_required')])
            ->assertSessionMissing('customer');
    }

    public function test_invalid_otp_and_missing_backend_profile_cannot_start_a_customer_session(): void
    {
        Http::fake(['*/api/auth/verify-firebase-token' => Http::response(['message' => 'Invalid token'], 401)]);
        $this->post(route('customer.authenticate'), ['firebase_token' => 'invalid-token'])
            ->assertRedirect(route('customer.login'))->assertSessionHasErrors('login')->assertSessionMissing('customer');
    }

    public function test_account_displays_current_promo_and_disables_purchasing_without_exposing_tokens(): void
    {
        $this->backend(['source' => 'PROMO', 'status' => 'ACTIVE', 'end_at' => now()->addMonth()->toIso8601String(), 'offer' => ['type' => 'PROMO_PACKAGE', 'name' => 'Founder Creator']]);
        $this->withSession($this->sessionData())->get(route('customer.dashboard'))->assertOk()
            ->assertSee('Founder Creator')->assertSee(__('account.package_info'))->assertDontSee('private-backend-token');
        Http::fake(['*/api/v1/legal/me' => Http::response(['data' => ['purchase' => []]]), '*/api/v1/internal/purchase-eligibility' => Http::response(['data' => ['allowed' => false, 'reason' => 'ACTIVE_PROMO', 'message' => 'Promo in corso.']]), '*/api/v1/plans' => Http::response(['data' => [['active' => true, 'providers' => [['provider' => 'STRIPE', 'active' => true, 'external_price_id' => 'price_test']]]]])]);
        $this->postJson(route('subscribe.checkout'), ['plan' => 'price_test', 'purchase_terms_accepted' => true, 'recurring_terms_accepted' => true])
            ->assertUnprocessable()->assertJsonPath('error', 'Promo in corso.');
        Http::assertNotSent(fn ($request) => str_ends_with($request->url(), '/checkout-sessions'));
    }

    public static function portalVisibility(): array
    {
        return [['STRIPE', 'PLAN', true], ['STRIPE', 'PROMO_PACKAGE', false], ['PROMO', 'PROMO_PACKAGE', false], ['APPLE', 'PLAN', false], ['GOOGLE', 'PLAN', false], ['MANUAL', 'MANUAL', false]];
    }

    #[DataProvider('portalVisibility')]
    public function test_only_recurring_stripe_plans_show_the_portal(string $source, string $type, bool $visible): void
    {
        $this->backend(['source' => $source, 'status' => 'ACTIVE', 'auto_renew' => true, 'offer' => ['type' => $type, 'name' => 'Commercial offer']]);
        $response = $this->withSession($this->sessionData())->get(route('customer.dashboard'))->assertOk()->assertSee('Commercial offer');
        if ($visible) {
            $response->assertSee('action="'.route('customer.portal').'"', false)->assertSee(__('account.changes_immediate'));
        } else {
            $response->assertDontSee('action="'.route('customer.portal').'"', false);
        }
    }

    public function test_portal_uses_authenticated_uuid_and_server_return_url_with_hmac(): void
    {
        $this->backend();
        Http::fake(['*/api/v1/internal/billing-portal-sessions' => Http::response(['data' => ['allowed' => true, 'session' => ['url' => 'https://billing.stripe.com/p/session/test']]])]);
        $this->withSession($this->sessionData())->post(route('customer.portal'), ['user' => ['id' => 'attacker'], 'return_url' => 'https://attacker.test'])
            ->assertRedirect('https://billing.stripe.com/p/session/test');
        Http::assertSent(function ($request) {
            if (! str_ends_with($request->url(), '/billing-portal-sessions')) return false;
            $timestamp = $request->header('X-Wayout-Timestamp')[0];
            return $request['user']['id'] === $this->profile()['id'] && $request['return_url'] === route('customer.dashboard')
                && ! isset($request['external_reference']) && ! $request->hasHeader('Authorization')
                && hash_equals(hash_hmac('sha256', $timestamp.'.'.$request->body(), str_repeat('s', 48)), $request->header('X-Wayout-Signature')[0]);
        });
    }

    public function test_portal_rejection_is_displayed_and_never_opens_a_second_checkout(): void
    {
        $this->backend();
        Http::fake(['*/api/v1/internal/billing-portal-sessions' => Http::response(['data' => ['allowed' => false, 'reason' => 'STORE_SUBSCRIPTION', 'message' => 'Gestisci dallo store.']])]);
        $this->withSession($this->sessionData())->post(route('customer.portal'))->assertRedirect(route('customer.dashboard'))->assertSessionHas('account_error', 'Gestisci dallo store.');
        $this->get(route('customer.dashboard'))->assertOk()->assertSee('Gestisci dallo store.');
        Http::assertNotSent(fn ($request) => str_ends_with($request->url(), '/checkout-sessions'));
    }

    public function test_portal_outage_keeps_the_account_available(): void
    {
        $this->backend();
        Http::fake(['*/api/v1/internal/billing-portal-sessions' => Http::response([], 503)]);
        $this->withSession($this->sessionData())->post(route('customer.portal'))->assertRedirect(route('customer.dashboard'))->assertSessionHas('account_error', __('account.portal_unavailable'));
    }

    public function test_invalid_portal_url_is_rejected(): void
    {
        $this->backend();
        Http::fake(['*/api/v1/internal/billing-portal-sessions' => Http::response(['data' => ['allowed' => true, 'session' => ['url' => 'javascript:alert(1)']]])]);
        $this->withSession($this->sessionData())->post(route('customer.portal'))->assertRedirect(route('customer.dashboard'))->assertSessionHas('account_error', __('account.portal_unavailable'));
    }

    public function test_history_uses_customer_bearer_and_displays_expired_offers_without_store_tokens(): void
    {
        Http::fake([
            '*/api/v1/users/me' => Http::response(['data' => $this->profile()]),
            '*/api/v1/internal/users/*/subscription' => Http::response(['data' => ['has_active_entitlement' => false, 'subscription' => null, 'offer' => null]]),
            '*/api/v1/me/subscription/history' => Http::response(['data' => [[
                'source' => 'APPLE', 'status' => 'EXPIRED', 'offer' => ['name' => 'Old Apple plan'],
                'start_at' => '2025-01-01T00:00:00Z', 'end_at' => '2025-02-01T00:00:00Z', 'auto_renew' => false,
                'apple_original_tx_id' => 'private-store-token',
            ]]]),
            '*/api/v1/internal/promo-packages' => Http::response(['data' => []]),
        ]);
        $this->withSession($this->sessionData())->get(route('customer.dashboard'))->assertOk()->assertSee('Old Apple plan')->assertSee('01/02/2025')->assertDontSee('private-store-token')->assertDontSee('private-backend-token')->assertDontSee('purchaseDeadline');
        Http::assertSent(fn ($request) => str_ends_with($request->url(), '/subscription/history') && $request->hasHeader('Authorization', 'Bearer private-backend-token') && ! $request->hasHeader('X-Wayout-Signature'));
    }

    public function test_returning_from_the_portal_reads_the_updated_backend_plan(): void
    {
        Http::fake([
            '*/api/v1/users/me' => Http::response(['data' => $this->profile()]),
            '*/api/v1/internal/users/*/subscription' => Http::sequence()
                ->push(['data' => ['has_active_entitlement' => true, 'subscription' => ['source' => 'STRIPE', 'status' => 'ACTIVE'], 'offer' => ['type' => 'PLAN', 'name' => 'Previous plan']]])
                ->push(['data' => ['has_active_entitlement' => true, 'subscription' => ['source' => 'STRIPE', 'status' => 'ACTIVE'], 'offer' => ['type' => 'PLAN', 'name' => 'Updated plan']]]),
            '*/api/v1/me/subscription/history' => Http::response(['data' => []]),
            '*/api/v1/internal/promo-packages' => Http::response(['data' => []]),
            '*/api/v1/internal/billing-portal-sessions' => Http::response(['data' => ['allowed' => true, 'session' => ['url' => 'https://billing.stripe.com/p/session/test']]]),
        ]);
        $this->withSession($this->sessionData())->get(route('customer.dashboard'))->assertOk()->assertSee('Previous plan');
        $this->post(route('customer.portal'))->assertRedirect('https://billing.stripe.com/p/session/test');
        $this->get(route('customer.dashboard'))->assertOk()->assertSee('Updated plan')->assertDontSee('Previous plan');
    }

    public function test_history_failure_is_not_mistaken_for_empty_history(): void
    {
        Http::fake([
            '*/api/v1/users/me' => Http::response(['data' => $this->profile()]),
            '*/api/v1/internal/users/*/subscription' => Http::response(['data' => ['has_active_entitlement' => false, 'subscription' => null, 'offer' => null]]),
            '*/api/v1/me/subscription/history' => Http::response([], 503),
            '*/api/v1/internal/promo-packages' => Http::response(['data' => []]),
        ]);
        $this->withSession($this->sessionData())->get(route('customer.dashboard'))->assertOk()->assertSee(__('account.history_unavailable'))->assertDontSee(__('account.history_empty'));
    }

    public static function backendPurchaseDecisions(): array
    {
        return [['PROMO', 'ACTIVE', true], ['PROMO', 'IN_GRACE', true], ['APPLE', 'IN_GRACE', true], ['GOOGLE', 'EXPIRED', false]];
    }

    #[DataProvider('backendPurchaseDecisions')]
    public function test_backend_verdict_controls_both_buttons_and_checkout(string $source, string $status, bool $allowed): void
    {
        $this->backend(['source' => $source, 'status' => $status, 'end_at' => '2020-01-01T00:00:00Z', 'offer' => ['type' => 'PROMO_PACKAGE', 'name' => 'Free package']]);
        Http::fake([
            '*/api/v1/plans' => Http::response(['data' => [[
                'active' => true, 'name' => 'Monthly', 'price' => '99.00', 'currency' => 'USD',
                'providers' => [['provider' => 'STRIPE', 'active' => true, 'external_price_id' => 'price_monthly', 'price' => '12.00', 'currency' => 'EUR']],
            ]]]),
            '*/api/v1/legal/me' => Http::response(['data' => ['purchase' => []]]),
            '*/api/v1/internal/purchase-eligibility' => Http::response(['data' => ['allowed' => $allowed, 'reason' => $allowed ? null : 'ACTIVE_SUBSCRIPTION', 'message' => $allowed ? null : 'Acquisto non consentito.']]),
            '*/api/v1/internal/checkout-sessions' => Http::response(['data' => ['allowed' => true, 'session' => ['id' => 'cs_monthly', 'url' => 'https://checkout.stripe.test/monthly']]]),
        ]);
        $this->withSession($this->sessionData())->getJson(route('customer.plans'))->assertOk()
            ->assertJsonPath('plans.0.eligibility.allowed', $allowed)->assertJsonPath('plans.0.price', '12.00')->assertJsonPath('plans.0.currency', 'EUR');
        $response = $this->postJson(route('subscribe.checkout'), ['plan' => 'price_monthly', 'purchase_terms_accepted' => true, 'recurring_terms_accepted' => true]);
        if ($allowed) {
            $response->assertOk()->assertJsonPath('redirectUrl', 'https://checkout.stripe.test/monthly');
            $this->assertDatabaseHas('purchases', ['plan' => 'price_monthly', 'amount' => 1200, 'currency' => 'eur']);
        } else {
            $response->assertUnprocessable()->assertJsonPath('error', 'Acquisto non consentito.');
            Http::assertNotSent(fn ($request) => str_ends_with($request->url(), '/checkout-sessions'));
        }
    }

    public function test_a_valid_signed_order_url_does_not_expose_another_users_order(): void
    {
        $this->backend();
        $id = DatabaseUuid::new();
        DB::table('purchases')->insert([
            'id' => $id, 'wayout_user_id' => '32131864-6d52-4b97-919d-99d4f2abc846',
            'email' => 'other@example.com', 'plan' => 'join', 'amount' => 100, 'currency' => 'eur', 'status' => 'pending',
            'created_at' => now(), 'updated_at' => now(),
        ]);
        $url = URL::temporarySignedRoute('checkout.success', now()->addMinute(), ['purchase' => $id]);
        $this->withSession($this->sessionData())->get($url)->assertNotFound();
        $url = URL::temporarySignedRoute('checkout.status', now()->addMinute(), ['purchase' => $id]);
        $this->getJson($url)->assertNotFound();
    }

    public function test_backend_auth_rejection_and_local_expiry_both_require_new_login(): void
    {
        $this->backend(status: 401);
        $this->withSession($this->sessionData())->get(route('customer.dashboard'))->assertRedirect(route('customer.login'))->assertSessionMissing('customer');
        $data = $this->sessionData();
        $data['customer']['expires_at'] = now()->subSecond()->timestamp;
        $this->withSession($data)->postJson(route('subscribe.checkout'), [])->assertUnauthorized();
    }

    public function test_backend_outage_fails_closed_without_discarding_customer_session(): void
    {
        $this->backend(status: 503);
        $this->withSession($this->sessionData())->get(route('customer.dashboard'))->assertStatus(503)->assertSessionHas('customer');
        $this->postJson(route('subscribe.checkout'), [])->assertStatus(503);
    }

    public function test_account_catalog_uses_backend_verdicts_even_without_a_current_subscription(): void
    {
        $this->backend();
        Http::fake([
            '*/api/v1/plans' => Http::response(['data' => [[
                'name' => 'Mensile', 'active' => true, 'price' => '12.00', 'currency' => 'EUR',
                'providers' => [['provider' => 'STRIPE', 'active' => true, 'external_price_id' => 'price_monthly']],
            ]]]),
            '*/api/v1/internal/purchase-eligibility' => Http::response(['data' => ['allowed' => false, 'reason' => 'ACTIVE_PROMO', 'message' => 'Pacchetto già acquistato.']]),
        ]);
        $this->withSession($this->sessionData())->getJson(route('customer.plans'))->assertOk()
            ->assertJsonPath('plans.0.eligibility.allowed', false)
            ->assertJsonPath('plans.0.eligibility.reason', 'ACTIVE_PROMO')
            ->assertHeader('Cache-Control', 'no-store, private');
        Http::assertSent(fn ($request) => str_ends_with($request->url(), '/purchase-eligibility')
            && $request['user']['id'] === $this->profile()['id']
            && $request['external_price_id'] === 'price_monthly'
            && $request->hasHeader('X-Wayout-Signature'));
    }

    public function test_invalid_subscription_response_never_enables_purchases(): void
    {
        Http::fake([
            '*/api/v1/users/me' => Http::response(['data' => $this->profile()]),
            '*/api/v1/internal/users/*/subscription' => Http::response([]),
        ]);
        $this->withSession($this->sessionData())->get(route('customer.dashboard'))->assertStatus(503);
        Http::assertNotSent(fn ($request) => str_ends_with($request->url(), '/checkout-sessions'));
    }

    public function test_existing_app_user_can_sign_in_pay_for_a_promo_and_receive_backend_confirmation(): void
    {
        \Illuminate\Support\Facades\Mail::fake();
        $offerId = '32131864-6d52-4b97-919d-99d4f2abc846';
        Http::fake([
            '*/api/auth/verify-firebase-token' => Http::response(['data' => ['access_token' => 'private-backend-token']]),
            '*/api/v1/users/me' => Http::response(['data' => $this->profile()]),
            '*/api/v1/me/subscription/history' => Http::response(['data' => []]),
            '*/api/v1/legal/me' => Http::response(['data' => ['purchase' => []]]),
            '*/api/v1/internal/promo-packages' => Http::response(['data' => [[
                'id' => $offerId, 'code' => 'FOUNDER_JOIN_12M_PASS', 'name' => 'Founder Join',
                'price' => '29.90', 'currency' => 'EUR', 'duration_days' => 365, 'free' => false, 'available' => 10,
            ]]]),
            '*/api/v1/internal/purchase-eligibility' => Http::response(['data' => ['allowed' => true]]),
            '*/api/v1/internal/checkout-sessions' => Http::response(['data' => [
                'allowed' => true, 'session' => ['id' => 'cs_promo', 'url' => 'https://checkout.stripe.test/promo'],
            ]]),
            '*/api/v1/internal/users/*/subscription' => Http::response(['data' => [
                'has_active_entitlement' => true, 'offer' => ['id' => $offerId, 'type' => 'PROMO_PACKAGE', 'name' => 'Founder Join'],
                'subscription' => ['source' => 'STRIPE', 'status' => 'ACTIVE', 'stripe_subscription_id' => 'cs_promo',
                    'start_at' => now()->toIso8601String(), 'end_at' => now()->addYear()->toIso8601String()],
            ]]),
        ]);
        $this->post(route('customer.authenticate'), ['firebase_token' => 'valid-firebase-token'])
            ->assertRedirect(route('customer.dashboard'));
        $this->get(route('customer.dashboard'))->assertOk()->assertSee('Founder Join');
        $response = $this->postJson(route('subscribe.checkout'), [
            'plan' => 'FOUNDER_JOIN_12M_PASS', 'purchase_terms_accepted' => true,
        ])->assertOk()->assertJsonPath('redirectUrl', 'https://checkout.stripe.test/promo');
        $purchaseId = $response->json('purchaseId');
        $this->assertDatabaseHas('purchases', ['id' => $purchaseId, 'wayout_user_id' => $this->profile()['id'],
            'promo_package_code' => 'FOUNDER_JOIN_12M_PASS', 'stripe_session_id' => 'cs_promo', 'status' => 'pending']);
        Http::assertSent(fn ($request) => str_ends_with($request->url(), '/checkout-sessions')
            && $request['user']['id'] === $this->profile()['id']
            && $request['promo_package_code'] === 'FOUNDER_JOIN_12M_PASS'
            && ! isset($request['external_price_id']) && ! empty($request['external_reference']));
        $this->get(URL::temporarySignedRoute('checkout.success', now()->addMinute(), ['purchase' => $purchaseId]))->assertOk();
        $this->assertDatabaseHas('purchases', ['id' => $purchaseId, 'status' => 'succeeded']);
        \Illuminate\Support\Facades\Mail::assertSent(\App\Mail\PurchaseConfirmationMail::class, fn ($mail) => $mail->hasTo('ada@example.com'));
        Http::assertNotSent(fn ($request) => str_ends_with($request->url(), '/create-profile'));
    }

    public function test_logout_invalidates_the_customer_session(): void
    {
        $this->withSession($this->sessionData())->post(route('customer.logout'))->assertRedirect(route('customer.login'))->assertSessionMissing('customer');
    }
}
