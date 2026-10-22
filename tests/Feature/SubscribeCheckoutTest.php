<?php

namespace Tests\Feature;

use App\Mail\PurchaseConfirmationMail;
use App\Support\DatabaseUuid;
use App\Support\LegalDocumentService;
use App\Support\PurchasePackage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class SubscribeCheckoutTest extends TestCase
{
    use RefreshDatabase;

    public function test_sales_terms_use_the_canonical_url(): void
    {
        $this->get('/termini-di-vendita')->assertOk();
        $this->get('/condizioni-di-vendita')->assertRedirect('/termini-di-vendita')->assertStatus(301);
    }

    public function test_plans_are_public_and_old_subscribe_url_redirects(): void
    {
        $this->fakeCatalog();
        $this->get('/piani')->assertOk()->assertDontSee('Waitlist Pass');
        $this->get('/subscribe')->assertRedirect('/piani')->assertStatus(301);
        $this->get('/')->assertOk()->assertSee('Prossimamente')->assertDontSee('waitlist-join-form');
        $this->post('/waitlist', ['email' => 'member@example.com'])->assertNotFound();
        $this->post('/subscribe/access')->assertNotFound();
    }

    public function test_subscribe_page_uses_only_the_two_paid_founder_packages(): void
    {
        $this->fakeCatalog();
        config()->set('services.firebase.client.measurement_id', 'G-TEST123');

        $response = $this->withSession(['subscribe_entry_allowed' => true])->get(route('subscribe'));

        $response->assertOk()
            ->assertSee('29,90')
            ->assertSee('59,90')
            ->assertSee('37 disponibili')
            ->assertSee('name="gender"', false)
            ->assertSee('id="payment-phone-send"', false)
            ->assertSee('data-resend-seconds="60"', false)
            ->assertSee('Reinvia tra :seconds s')
            ->assertSee('id="payment-phone-verify"', false)
            ->assertSeeInOrder(['window.wayoutFirebaseConfig', 'measurementId', 'G-TEST123'], false)
            ->assertDontSee('WAITLIST_60D_PASS')
            ->assertDontSee('js.stripe.com', false);
    }

    public function test_packages_explain_the_one_shot_offer_and_activation_duration(): void
    {
        $this->fakeCatalog();
        $this->get(route('subscribe'))->assertOk()
            ->assertSee('Pagamento unico')->assertSee('365 giorni')
            ->assertSee('Nessun rinnovo automatico')->assertSee('Dall’acquisto/attivazione')
            ->assertDontSee('Waitlist Pass')->assertDontSee('prima del lancio');
    }

    public function test_catalog_grid_supports_ten_arbitrary_packages_and_excludes_free_and_inactive_ones(): void
    {
        $packages = [];
        for ($index = 1; $index <= 10; $index++) {
            $packages[] = ['code' => 'WEB_'.$index, 'name' => 'Web package '.$index, 'price' => '19.99', 'currency' => 'EUR', 'duration_days' => 30 * $index, 'free' => false, 'available' => $index === 5 ? 0 : null];
        }
        $packages[] = ['code' => 'FREE_PACKAGE', 'free' => true];
        $packages[] = ['code' => 'INACTIVE_PACKAGE', 'free' => false, 'active' => false];
        Http::fake(['*/internal/promo-packages' => Http::response(['data' => $packages])]);
        $response = $this->get(route('subscribe'))->assertOk()->assertSee('Web package 10')->assertSee('300 giorni')->assertSee('Esaurito')->assertDontSee('FREE_PACKAGE')->assertDontSee('INACTIVE_PACKAGE');
        $this->assertSame(10, substr_count($response->getContent(), 'data-package-code='));
    }

    public function test_empty_catalog_has_a_distinct_empty_state(): void
    {
        Http::fake(['*/internal/promo-packages' => Http::response(['data' => []])]);
        $this->get(route('subscribe'))->assertOk()->assertSee('Le prossime occasioni stanno arrivando.')->assertDontSee('data-package-code=', false);
    }

    public function test_checkout_accepts_arbitrary_catalog_codes_and_saves_the_package_snapshot(): void
    {
        $userId = '32131864-6d52-4b97-919d-99d4f2abc845';
        Http::fake(function (Request $request) use ($userId) {
            return match (parse_url($request->url(), PHP_URL_PATH)) {
                '/api/v1/internal/promo-packages' => Http::response(['data' => [['code' => 'CITY_90', 'name' => 'City 90', 'price' => '79.00', 'currency' => 'EUR', 'duration_days' => 90, 'free' => false, 'available' => null]]]),
                '/api/auth/verify-firebase-token' => Http::response(['data' => ['access_token' => 'existing-token']]),
                '/api/v1/users/me' => Http::response(['data' => ['id' => $userId]]),
                '/api/v1/internal/purchase-eligibility' => Http::response(['data' => ['allowed' => true]]),
                '/api/v1/internal/checkout-sessions' => Http::response(['data' => ['allowed' => true, 'session' => ['id' => 'cs_city', 'url' => 'https://checkout.stripe.test/city']]]),
                default => Http::response([], 404),
            };
        });
        $this->postJson(route('subscribe.checkout'), $this->checkoutPayload('CITY_90'))->assertOk();
        $this->assertDatabaseHas('purchases', ['plan' => 'CITY_90', 'promo_package_code' => 'CITY_90', 'package_name' => 'City 90', 'package_duration_days' => 90, 'amount' => 7900, 'package_start_at' => null]);
        Http::assertSent(fn (Request $request) => str_ends_with($request->url(), '/checkout-sessions') && $request['promo_package_code'] === 'CITY_90');
        $this->assertSame('City 90', PurchasePackage::name(DB::table('purchases')->first()));
    }

    public function test_checkout_rejects_codes_not_in_the_catalog(): void
    {
        $this->fakeCatalog();
        $this->postJson(route('subscribe.checkout'), $this->checkoutPayload('NOT_IN_CATALOG'))->assertUnprocessable();
        Http::assertNotSent(fn (Request $request) => str_ends_with($request->url(), '/checkout-sessions'));
        $this->assertDatabaseCount('purchases', 0);
    }

    public function test_catalog_failure_is_not_reported_as_sold_out(): void
    {
        Http::fake([
            'https://staging-app.wayoutapp.test/api/v1/internal/promo-packages' => Http::response([], 403),
        ]);

        $this->withSession(['subscribe_entry_allowed' => true])
            ->get(route('subscribe'))
            ->assertOk()
            ->assertSee('Non riusciamo a caricare i pacchetti.')
            ->assertDontSee('I Founder Pass sono esauriti.');
    }

    public function test_checkout_requires_a_valid_email_without_waitlist_context(): void
    {
        Http::preventStrayRequests();
        $payload = $this->checkoutPayload();
        unset($payload['email']);
        $this->postJson(route('subscribe.checkout'), $payload)->assertUnprocessable()->assertJsonStructure(['errors' => ['email']]);
        $payload['email'] = 'invalid-address';
        $this->postJson(route('subscribe.checkout'), $payload)->assertUnprocessable()->assertJsonStructure(['errors' => ['email']]);
        $this->assertDatabaseCount('purchases', 0);
    }

    public function test_checkout_creates_backend_session_for_an_existing_wayout_user(): void
    {
        [$entry, $userId] = $this->fakeExistingUserCheckout();

        $response = $this->withSession($this->checkoutSession($entry))
            ->postJson(route('subscribe.checkout'), $this->checkoutPayload('creator'));

        $response->assertOk()->assertJson([
            'redirectUrl' => 'https://checkout.stripe.test/session',
        ]);

        $this->assertDatabaseHas('purchases', [
            'email' => 'founder@example.com',
            'plan' => 'creator',
            'promo_package_code' => 'FOUNDER_CREATOR_12M_PASS',
            'wayout_user_id' => $userId,
            'amount' => 5990,
            'status' => 'pending',
        ]);

        Http::assertSent(function (Request $request): bool {
            if (! str_ends_with($request->url(), '/api/v1/internal/checkout-sessions')) {
                return false;
            }

            $body = $request->data();

            return ($body['promo_package_code'] ?? null) === 'FOUNDER_CREATOR_12M_PASS'
                && str_contains((string) ($body['success_url'] ?? ''), '/checkout/success/')
                && filled($request->header('X-Wayout-Signature')[0] ?? null);
        });
    }

    public function test_checkout_creates_a_new_profile_with_gender_verified_phone_and_generated_nickname(): void
    {
        $entry = $this->createVerifiedWaitlistEntry('founder@example.com');
        $userId = '90c5cf65-226a-4808-91a7-d7b22930a1e4';
        $profileRequest = null;

        Http::fake(function (Request $request) use ($userId, &$profileRequest) {
            $path = parse_url($request->url(), PHP_URL_PATH);

            return match ($path) {
                '/api/v1/internal/promo-packages' => Http::response(['data' => $this->promoPackages()]),
                '/api/auth/verify-firebase-token' => Http::response(['data' => []], 200, ['X-Temp-Token' => 'temporary-profile-token']),
                '/api/auth/create-profile' => (function () use ($request, &$profileRequest) {
                    $profileRequest = $request;

                    return Http::response(['data' => ['access_token' => 'new-user-token']]);
                })(),
                '/api/v1/users/me' => Http::response(['data' => ['id' => $userId]]),
                '/api/v1/internal/purchase-eligibility' => Http::response(['data' => ['allowed' => true]]),
                '/api/v1/internal/checkout-sessions' => Http::response(['data' => ['allowed' => true, 'session' => ['id' => 'cs_new', 'url' => 'https://checkout.stripe.test/new']]]),
                default => Http::response([], 404),
            };
        });

        $this->withSession($this->checkoutSession($entry))
            ->postJson(route('subscribe.checkout'), $this->checkoutPayload('join'))
            ->assertOk();

        $this->assertNotNull($profileRequest);
        $profile = $profileRequest->data();
        $this->assertSame('FEMALE', $profile['gender']);
        $this->assertSame('+393331234567', $profile['mobile_number']);
        $this->assertMatchesRegularExpression('/^@matteo_sportel_[a-z0-9]{4}$/', $profile['nickname']);
        $this->assertSame('temporary-profile-token', $profileRequest->header('X-Temp-Token')[0]);
    }

    public function test_backend_eligibility_rejection_prevents_checkout_session(): void
    {
        $entry = $this->createVerifiedWaitlistEntry('founder@example.com');
        $userId = '3f143fe2-c9e5-4698-809d-72c80c563e0c';

        Http::fake(function (Request $request) use ($userId) {
            $path = parse_url($request->url(), PHP_URL_PATH);

            return match ($path) {
                '/api/v1/internal/promo-packages' => Http::response(['data' => $this->promoPackages()]),
                '/api/auth/verify-firebase-token' => Http::response(['data' => ['access_token' => 'existing-token']]),
                '/api/v1/users/me' => Http::response(['data' => ['id' => $userId]]),
                '/api/v1/internal/purchase-eligibility' => Http::response(['data' => ['allowed' => false, 'reason' => 'ALREADY_ACTIVE', 'message' => 'Promo non disponibile.']]),
                default => Http::response([], 404),
            };
        });

        $this->withSession($this->checkoutSession($entry))
            ->postJson(route('subscribe.checkout'), $this->checkoutPayload())
            ->assertUnprocessable()
            ->assertJson(['reason' => 'ALREADY_ACTIVE']);

        Http::assertNotSent(fn (Request $request): bool => str_ends_with($request->url(), '/checkout-sessions'));
        $this->assertDatabaseCount('purchases', 0);
    }

    public function test_signed_success_confirms_entitlement_and_sends_confirmation_email(): void
    {
        Mail::fake();
        config()->set('services.qonto.enabled', false);
        $entry = $this->createVerifiedWaitlistEntry('founder@example.com');
        $purchaseId = DatabaseUuid::new();
        $userId = '0ea57493-fdcc-4093-965a-8a7774679116';

        DB::table('purchases')->insert([
            'id' => $purchaseId,
            'waitlist_entry_id' => $entry->id,
            'wayout_user_id' => $userId,
            'promo_package_code' => 'FOUNDER_JOIN_12M_PASS',
            'email' => 'founder@example.com',
            'first_name' => 'Matteo',
            'last_name' => 'Sportelli',
            'birth_date' => '1990-01-01',
            'plan' => 'join',
            'amount' => 2990,
            'currency' => 'eur',
            'order_reference' => 'WYO-TEST123',
            'status' => 'pending',
            'invoice_requested' => false,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        Http::fake([
            "https://staging-app.wayoutapp.test/api/v1/internal/users/{$userId}/subscription" => Http::response([
                'data' => [
                    'has_active_entitlement' => true,
                    'subscription' => ['source' => 'STRIPE', 'stripe_subscription_id' => 'sub_123', 'start_at' => '2026-10-21T12:00:00Z', 'end_at' => '2027-10-21T12:00:00Z'],
                ],
            ]),
        ]);

        $this->get(URL::temporarySignedRoute(
            'checkout.success', now()->addMinute(), ['purchase' => $purchaseId]
        ))->assertOk();

        $this->assertDatabaseHas('purchases', [
            'id' => $purchaseId,
            'status' => 'succeeded',
            'wayout_subscription_id' => 'sub_123',
            'package_start_at' => '2026-10-21 12:00:00',
            'package_end_at' => '2027-10-21 12:00:00',
        ]);

        $legalEvent = DB::table('consent_events')
            ->where('purchase_id', $purchaseId)
            ->where('consent_type', 'purchase_legal')
            ->first();

        $this->assertNotNull($legalEvent);

        $versions = json_decode($legalEvent->document_versions, true, flags: JSON_THROW_ON_ERROR);
        $hashes = json_decode($legalEvent->document_hashes, true, flags: JSON_THROW_ON_ERROR);
        $urls = json_decode($legalEvent->document_urls, true, flags: JSON_THROW_ON_ERROR);

        foreach (['sales', 'presale', 'passes', 'refunds', 'purchase_acceptance'] as $document) {
            $this->assertSame(
                app(LegalDocumentService::class)->current($document, $legalEvent->locale)->version,
                $versions[$document],
            );
            $this->assertSame(64, strlen($hashes[$document]));
            $this->assertNotEmpty($urls[$document]);
        }

        Mail::assertSent(PurchaseConfirmationMail::class);
    }

    private function fakeCatalog(): void
    {
        Http::fake([
            'https://staging-app.wayoutapp.test/api/v1/internal/promo-packages' => Http::response(['data' => $this->promoPackages()]),
        ]);
    }

    /** @return array{object, string} */
    private function fakeExistingUserCheckout(): array
    {
        $entry = $this->createVerifiedWaitlistEntry('founder@example.com');
        $userId = '32131864-6d52-4b97-919d-99d4f2abc845';

        Http::fake(function (Request $request) use ($userId) {
            return match (parse_url($request->url(), PHP_URL_PATH)) {
                '/api/v1/internal/promo-packages' => Http::response(['data' => $this->promoPackages()]),
                '/api/auth/verify-firebase-token' => Http::response(['data' => ['access_token' => 'existing-token']]),
                '/api/v1/users/me' => Http::response(['data' => ['id' => $userId]]),
                '/api/v1/internal/purchase-eligibility' => Http::response(['data' => ['allowed' => true]]),
                '/api/v1/internal/checkout-sessions' => Http::response(['data' => ['allowed' => true, 'session' => ['id' => 'cs_123', 'url' => 'https://checkout.stripe.test/session']]]),
                default => Http::response([], 404),
            };
        });

        return [$entry, $userId];
    }

    private function checkoutSession(object $entry): array
    {
        return [];
    }

    private function checkoutPayload(string $plan = 'join'): array
    {
        return [
            'email' => 'founder@example.com',
            'plan' => $plan,
            'firebase_token' => $this->firebaseToken('+393331234567'),
            'first_name' => 'Matteo',
            'last_name' => 'Sportelli',
            'birth_date' => '1990-01-01',
            'gender' => 'FEMALE',
            'invoice_requested' => false,
            'purchase_terms_accepted' => true,
        ];
    }

    private function firebaseToken(string $phone): string
    {
        $payload = rtrim(strtr(base64_encode(json_encode(['phone_number' => $phone], JSON_THROW_ON_ERROR)), '+/', '-_'), '=');

        return 'header.'.$payload.'.signature';
    }

    private function promoPackages(): array
    {
        return [
            ['code' => 'FOUNDER_JOIN_12M_PASS', 'name' => 'Founder Join', 'price' => '29.90', 'currency' => 'EUR', 'available' => 37, 'max_available_quantity' => 500, 'duration_days' => 365, 'free' => false],
            ['code' => 'FOUNDER_CREATOR_12M_PASS', 'name' => 'Founder Creator', 'price' => '59.90', 'currency' => 'EUR', 'available' => null, 'max_available_quantity' => -1, 'duration_days' => 365, 'free' => false],
            ['code' => 'WAITLIST_60D_PASS', 'name' => 'Waitlist', 'price' => '0.00', 'currency' => 'EUR', 'available' => null, 'max_available_quantity' => -1, 'free' => true],
        ];
    }
}
