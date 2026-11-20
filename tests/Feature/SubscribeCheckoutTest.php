<?php

namespace Tests\Feature;

use App\Mail\PurchaseConfirmationMail;
use App\Support\DatabaseUuid;
use App\Support\LegalDocumentService;
use App\Support\PurchasePackage;
use App\Support\WayoutApiClient;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class SubscribeCheckoutTest extends TestCase
{
    use RefreshDatabase;

    private array $purchaseDocuments = [];

    private string $customerId = '32131864-6d52-4b97-919d-99d4f2abc845';

    private function customerProfile(): array
    {
        return ['id' => $this->customerId, 'first_name' => 'Matteo', 'last_name' => 'Sportelli',
            'email' => 'founder@example.com', 'date_of_birth' => '1990-01-01', 'gender' => 'FEMALE'];
    }

    public function test_public_subscription_catalog_includes_only_active_web_purchases(): void
    {
        Http::fake(['*/api/v1/plans' => Http::response(['data' => [
            ['name' => 'Mensile web', 'active' => true, 'providers' => [['provider' => 'STRIPE', 'active' => true, 'external_price_id' => 'price_web']]],
            ['name' => 'Solo Apple', 'active' => true, 'providers' => [['provider' => 'APPLE', 'active' => true]]],
            ['name' => 'Solo Google', 'active' => true, 'providers' => [['provider' => 'GOOGLE', 'active' => true]]],
            ['name' => 'Stripe disattivato', 'active' => true, 'providers' => [['provider' => 'STRIPE', 'active' => false, 'external_price_id' => 'price_disabled']]],
            ['name' => 'Prezzo assente', 'active' => true, 'providers' => [['provider' => 'STRIPE', 'active' => true]]],
            ['name' => 'Piano disattivato', 'active' => false, 'providers' => [['provider' => 'STRIPE', 'active' => true, 'external_price_id' => 'price_inactive']]],
            ['name' => 'Web e app', 'active' => true, 'providers' => [
                ['provider' => 'APPLE', 'active' => true],
                ['provider' => 'STRIPE', 'active' => true, 'external_price_id' => 'price_both'],
            ]],
        ]])]);

        $this->getJson(route('subscribe.plans'))->assertOk()->assertJsonCount(2, 'plans')
            ->assertJsonPath('plans.0.name', 'Mensile web')->assertJsonPath('plans.0.external_price_id', 'price_web')
            ->assertJsonPath('plans.1.name', 'Web e app')->assertJsonPath('plans.1.external_price_id', 'price_both')
            ->assertDontSee('Solo Apple')->assertDontSee('Solo Google')->assertDontSee('Stripe disattivato')
            ->assertDontSee('Prezzo assente')->assertDontSee('Piano disattivato');
    }

    private int $legalAcceptStatus = 200;

    private int $legalReadStatus = 200;

    private ?array $sitePublications = null;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withSession(['customer' => [
            'token' => Crypt::encryptString('existing-token'),
            'expires_at' => now()->addHour()->timestamp,
            'profile' => $this->customerProfile(),
        ]]);
        Http::fake([
            '*/api/v1/users/me' => fn () => Http::response(['data' => $this->customerProfile()]),
            '*/api/v1/me/subscription' => Http::response(['data' => null]),
            '*/api/v1/me/entitlement' => Http::response(['data' => ['offer' => null, 'grace' => null]]),
        ]);

        $service = \Mockery::mock(LegalDocumentService::class, [app(WayoutApiClient::class)])->makePartial();
        $service->shouldReceive('published')->with('SITE')->andReturnUsing(fn () => $this->publishedPurchaseDocuments());
        $service->shouldReceive('published')->with(null)->passthru();
        $this->app->instance(LegalDocumentService::class, $service);
    }

    private function publishedPurchaseDocuments(): array
    {
        if ($this->sitePublications !== null) {
            return $this->sitePublications;
        }

        return array_map(fn (array $document) => [
            'code' => $document['code'] ?? 'TERMS',
            'name' => $document['name'] ?? 'Terms',
            'scope' => $document['scope'] ?? 'SITE',
            'current' => ['id' => $document['version_id']],
        ], $this->purchaseDocuments);
    }

    public function test_app_purchase_documents_are_not_presented_or_accepted_on_the_site(): void
    {
        $this->purchaseDocuments = [
            ['version_id' => DatabaseUuid::new(), 'scope' => 'APP', 'name' => 'Pass Abbonamenti Acquisti in APP'],
            ['version_id' => DatabaseUuid::new(), 'scope' => 'SITE', 'name' => 'Termini sito'],
        ];
        $this->fakeExistingUserCheckout();
        $payload = $this->checkoutPayload();
        $this->postJson(route('subscribe.checkout'), $payload)->assertOk()
            ->assertJsonCount(1, 'documents')
            ->assertJsonPath('documents.0.name', 'Termini sito')
            ->assertDontSee('Pass Abbonamenti Acquisti in APP');
        $payload['accepted_version_ids'] = [$this->purchaseDocuments[1]['version_id']];
        $this->postJson(route('subscribe.checkout'), $payload)->assertOk()->assertJsonStructure(['redirectUrl']);
        Http::assertSent(fn (Request $request) => str_ends_with($request->url(), '/internal/legal/accept')
            && $request['version_ids'] === [$this->purchaseDocuments[1]['version_id']]);
    }

    public function test_app_only_purchase_documents_do_not_add_a_second_consent_step(): void
    {
        $this->purchaseDocuments = [['version_id' => DatabaseUuid::new(), 'scope' => 'APP', 'name' => 'App terms']];
        $this->fakeExistingUserCheckout();
        $this->postJson(route('subscribe.checkout'), $this->checkoutPayload())->assertOk()->assertJsonStructure(['redirectUrl']);
        Http::assertNotSent(fn (Request $request) => str_ends_with($request->url(), '/internal/legal/accept'));
    }

    public function test_purchase_documents_without_scope_are_matched_by_published_site_version_id(): void
    {
        $this->purchaseDocuments = [
            ['version_id' => DatabaseUuid::new(), 'name' => 'App terms'],
            ['version_id' => DatabaseUuid::new(), 'name' => 'Site terms'],
        ];
        $this->sitePublications = [[
            'code' => 'TERMS', 'name' => 'Site terms', 'scope' => 'SITE',
            'current' => ['id' => $this->purchaseDocuments[1]['version_id']],
        ]];
        $this->fakeExistingUserCheckout();

        $this->postJson(route('subscribe.checkout'), $this->checkoutPayload())->assertOk()
            ->assertJsonCount(1, 'documents')
            ->assertJsonPath('documents.0.name', 'Site terms');
    }

    public function test_purchase_documents_must_be_presented_and_accepted_before_checkout(): void
    {
        $this->purchaseDocuments = [['version_id' => DatabaseUuid::new(), 'name' => 'Purchase terms', 'checkbox_label' => 'Accetto {link}', 'content_text' => 'Terms']];
        $this->fakeExistingUserCheckout();
        $payload = $this->checkoutPayload();
        // Even a forged checkbox cannot bypass presentation of the document.
        $payload['accepted_version_ids'] = [$this->purchaseDocuments[0]['version_id']];
        $this->postJson(route('subscribe.checkout'), $payload)->assertOk()->assertJsonPath('legal_required', true);
        Http::assertNotSent(fn (Request $request) => str_ends_with($request->url(), '/purchase-eligibility'));
        $this->postJson(route('subscribe.checkout'), $payload)->assertOk()->assertJsonStructure(['redirectUrl']);
        Http::assertSent(function (Request $request) {
            if (! str_ends_with($request->url(), '/internal/legal/accept')) {
                return false;
            }
            $timestamp = $request->header('X-Wayout-Timestamp')[0];

            return $request->hasHeader('Authorization', 'Bearer existing-token')
                && $request['version_ids'] === [$this->purchaseDocuments[0]['version_id']]
                && $request['client_ip'] === '127.0.0.1'
                && $request['context_ref'] === 'join'
                && $request->header('X-Wayout-Signature')[0] === hash_hmac('sha256', $timestamp.'.'.$request->body(), str_repeat('s', 48));
        });
    }

    public function test_legal_version_conflict_reloads_documents_and_blocks_checkout(): void
    {
        $this->purchaseDocuments = [['version_id' => DatabaseUuid::new(), 'name' => 'Terms']];
        $this->legalAcceptStatus = 409;
        $this->fakeExistingUserCheckout();
        $payload = $this->checkoutPayload();
        $this->postJson(route('subscribe.checkout'), $payload)->assertOk()->assertJsonPath('legal_required', true);
        $payload['accepted_version_ids'] = [$this->purchaseDocuments[0]['version_id']];
        $this->postJson(route('subscribe.checkout'), $payload)->assertStatus(409)->assertJsonPath('legal_required', true);
        Http::assertNotSent(fn (Request $request) => str_ends_with($request->url(), '/purchase-eligibility'));
        $this->assertDatabaseCount('purchases', 0);
    }

    public function test_empty_purchase_documents_skip_acceptance(): void
    {
        $this->fakeExistingUserCheckout();
        $this->purchaseDocuments = [];
        $this->postJson(route('subscribe.checkout'), $this->checkoutPayload())->assertOk();
        Http::assertNotSent(fn (Request $request) => str_ends_with($request->url(), '/internal/legal/accept'));
    }

    public function test_updated_purchase_version_requires_new_checkboxes(): void
    {
        $this->purchaseDocuments = [['version_id' => DatabaseUuid::new(), 'name' => 'Terms']];
        $this->fakeExistingUserCheckout();
        $payload = $this->checkoutPayload();
        $this->postJson(route('subscribe.checkout'), $payload)->assertOk();
        $payload['accepted_version_ids'] = [$this->purchaseDocuments[0]['version_id']];
        $this->purchaseDocuments[0]['version_id'] = DatabaseUuid::new();
        $this->postJson(route('subscribe.checkout'), $payload)->assertOk()
            ->assertJsonPath('legal_required', true)
            ->assertJsonPath('documents.0.version_id', $this->purchaseDocuments[0]['version_id']);
        Http::assertNotSent(fn (Request $request) => str_ends_with($request->url(), '/internal/legal/accept') || str_ends_with($request->url(), '/purchase-eligibility'));
    }

    public function test_failed_acceptance_never_starts_checkout(): void
    {
        $this->purchaseDocuments = [['version_id' => DatabaseUuid::new(), 'name' => 'Terms']];
        $this->legalAcceptStatus = 503;
        $this->fakeExistingUserCheckout();
        $payload = $this->checkoutPayload();
        $this->postJson(route('subscribe.checkout'), $payload)->assertOk();
        $payload['accepted_version_ids'] = [$this->purchaseDocuments[0]['version_id']];
        $this->postJson(route('subscribe.checkout'), $payload)->assertStatus(502);
        Http::assertNotSent(fn (Request $request) => str_ends_with($request->url(), '/purchase-eligibility'));
        $this->assertDatabaseCount('purchases', 0);
    }

    public function test_unavailable_legal_state_blocks_purchase(): void
    {
        $this->legalReadStatus = 503;
        $this->fakeExistingUserCheckout();
        $this->postJson(route('subscribe.checkout'), $this->checkoutPayload())->assertStatus(502);
        Http::assertNotSent(fn (Request $request) => str_ends_with($request->url(), '/purchase-eligibility'));
        $this->assertDatabaseCount('purchases', 0);
    }

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

    public function test_subscribe_page_separates_paid_promos_and_subscriptions_and_hides_free_offers(): void
    {
        $this->fakeCatalog();
        config()->set('services.firebase.client.measurement_id', 'G-TEST123');

        $response = $this->withSession(['subscribe_entry_allowed' => true])->get(route('subscribe'));

        $response->assertOk()
            ->assertSee('29,90')
            ->assertSee('59,90')
            ->assertSee('37 disponibili')
            ->assertSee('name="gender"', false)
            ->assertDontSee('id="payment-phone-send"', false)
            ->assertSee(route('customer.dashboard'))
            ->assertDontSee('WAITLIST_60D_PASS')
            ->assertSee('id="promo-offers"', false)
            ->assertSee('id="subscription-offers"', false)
            ->assertSee('Pacchetti promo one shot')
            ->assertSee('data-offer-tab="promo-offers"', false)
            ->assertSee('data-offer-tab="subscription-offers"', false)
            ->assertSee('aria-selected="true" aria-controls="promo-offers"', false)
            ->assertSee('aria-labelledby="subscriptions-tab" tabindex="0"  hidden', false)
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

    public function test_permission_copy_uses_localized_marketing_labels_and_preserves_limits(): void
    {
        $packages = $this->promoPackages();
        $packages[0]['tier']['permissions'] = [
            ['key' => 'CAN_CREATE_PRIVATE_PARTY', 'description' => 'Whether the user can create private party events', 'value_type' => 'BOOL', 'value' => true],
            ['key' => 'MAX_FUTURE_EVENTS', 'value_type' => 'INT', 'value' => 20],
            ['key' => 'AD_FREE', 'description' => 'Whether the user is exempt from ads in the app', 'value_type' => 'BOOL', 'value' => false],
        ];
        Http::fake(['*/internal/promo-packages' => Http::response(['data' => $packages])]);

        $this->get(route('subscribe', ['lang' => 'it']))->assertOk()
            ->assertSee('Organizza tavoli nei club e feste private')
            ->assertSee('Fino a <strong>20</strong> tavoli nei club e feste private in programma contemporaneamente', false)
            ->assertDontSee('Whether the user can create private party events')
            ->assertDontSee('Whether the user is exempt from ads in the app');
        $this->get(route('subscribe', ['lang' => 'en']))->assertOk()
            ->assertSee('Organizza tavoli nei club e feste private')
            ->assertSee('Fino a <strong>20</strong> tavoli nei club e feste private in programma contemporaneamente', false);
    }

    public function test_catalog_grid_supports_arbitrary_packages_including_free_and_excludes_inactive_ones(): void
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
        $this->get(route('subscribe'))->assertOk()->assertSee('Le prossime occasioni stanno arrivando.')
            ->assertSee('aria-selected="true" aria-controls="subscription-offers"', false)
            ->assertSee('aria-labelledby="promo-tab" tabindex="0"  hidden', false)
            ->assertDontSee('data-package-code=', false);
    }

    public function test_checkout_accepts_arbitrary_catalog_codes_and_saves_the_package_snapshot(): void
    {
        $userId = '32131864-6d52-4b97-919d-99d4f2abc845';
        Http::fake(function (Request $request) use ($userId) {
            return match (parse_url($request->url(), PHP_URL_PATH)) {
                '/api/v1/internal/promo-packages' => Http::response(['data' => [['code' => 'CITY_90', 'name' => 'City 90', 'price' => '79.00', 'currency' => 'EUR', 'duration_days' => 90, 'free' => false, 'available' => null]]]),
                '/api/auth/verify-firebase-token' => Http::response(['data' => ['access_token' => 'existing-token']]),
                '/api/v1/legal/me' => Http::response(['data' => ['purchase' => $this->purchaseDocuments]], $this->legalReadStatus),
                '/api/v1/internal/legal/accept' => Http::response(['data' => []], $this->legalAcceptStatus),
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

    public function test_checkout_uses_backend_profile_instead_of_submitted_identity(): void
    {
        $this->fakeExistingUserCheckout();
        $payload = $this->checkoutPayload();
        $payload['email'] = 'attacker@example.com';
        $payload['first_name'] = 'Attacker';
        $payload['firebase_token'] = 'different-identity-token';
        $this->postJson(route('subscribe.checkout'), $payload)->assertOk();
        $this->assertDatabaseHas('purchases', ['wayout_user_id' => $this->customerId, 'email' => 'founder@example.com', 'first_name' => 'Matteo']);
        Http::assertNotSent(fn (Request $request) => str_ends_with($request->url(), '/verify-firebase-token'));
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

    public function test_retrying_the_same_pending_purchase_reuses_the_order_and_backend_reference(): void
    {
        $this->fakeExistingUserCheckout();
        $first = $this->postJson(route('subscribe.checkout'), $this->checkoutPayload())->assertOk();
        $second = $this->postJson(route('subscribe.checkout'), $this->checkoutPayload())->assertOk();
        $this->assertSame($first->json('purchaseId'), $second->json('purchaseId'));
        $this->assertDatabaseCount('purchases', 1);
        $requests = Http::recorded(fn (Request $request) => str_ends_with($request->url(), '/checkout-sessions'))->values();
        $this->assertCount(2, $requests);
        $this->assertNotEmpty($requests[0][0]['external_reference']);
        $this->assertSame($requests[0][0]['external_reference'], $requests[1][0]['external_reference']);
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
                '/api/v1/legal/me' => Http::response(['data' => ['purchase' => $this->purchaseDocuments]], $this->legalReadStatus),
                '/api/v1/internal/legal/accept' => Http::response(['data' => []], $this->legalAcceptStatus),
                '/api/v1/users/me' => Http::response(['data' => ['id' => $userId]]),
                '/api/v1/internal/purchase-eligibility' => Http::response(['data' => ['allowed' => false, 'reason' => 'ACTIVE_SUBSCRIPTION', 'message' => 'Promo non disponibile.']]),
                default => Http::response([], 404),
            };
        });

        $this->withSession($this->checkoutSession($entry))
            ->postJson(route('subscribe.checkout'), $this->checkoutPayload())
            ->assertUnprocessable()
            ->assertJson(['reason' => 'ACTIVE_SUBSCRIPTION']);

        Http::assertNotSent(fn (Request $request): bool => str_ends_with($request->url(), '/checkout-sessions'));
        $this->assertDatabaseCount('purchases', 0);
    }

    public function test_signed_success_confirms_entitlement_and_sends_confirmation_email(): void
    {
        Mail::fake();
        config()->set('services.qonto.enabled', false);
        $entry = $this->createVerifiedWaitlistEntry('founder@example.com');
        $purchaseId = DatabaseUuid::new();
        $userId = $this->customerId;

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

    public function test_free_package_is_activated_without_a_stripe_checkout(): void
    {
        Http::fake(function (Request $request) {
            return match (parse_url($request->url(), PHP_URL_PATH)) {
                '/api/v1/internal/promo-packages' => Http::response(['data' => [['code' => 'FREE60', 'free' => true, 'duration_days' => 60, 'price' => null]]]),
                '/api/auth/verify-firebase-token' => Http::response(['data' => ['access_token' => 'existing-token']]),
                '/api/v1/legal/me' => Http::response(['data' => ['purchase' => $this->purchaseDocuments]], $this->legalReadStatus),
                '/api/v1/internal/legal/accept' => Http::response(['data' => []], $this->legalAcceptStatus),
                '/api/v1/users/me' => Http::response(['data' => ['id' => '32131864-6d52-4b97-919d-99d4f2abc845']]),
                '/api/v1/internal/purchase-eligibility' => Http::response(['data' => ['allowed' => true]]),
                '/api/v1/internal/promo-packages/activate' => Http::response(['data' => ['result' => 'activated']]),
                default => Http::response([], 404),
            };
        });
        $this->postJson(route('subscribe.checkout'), $this->checkoutPayload('FREE60'))->assertOk()->assertJsonStructure(['redirectUrl', 'purchaseId']);
        Http::assertNotSent(fn (Request $request) => str_ends_with($request->url(), '/checkout-sessions'));
        Http::assertSent(fn (Request $request) => str_ends_with($request->url(), '/promo-packages/activate') && $request['code'] === 'FREE60' && ! empty($request['external_reference']));
        $this->assertDatabaseHas('purchases', ['plan' => 'FREE60', 'amount' => 0, 'offer_type' => 'PROMO_PACKAGE']);
    }

    public function test_recurring_catalog_is_public_and_checkout_uses_external_price_id(): void
    {
        Http::fake(function (Request $request) {
            return match (parse_url($request->url(), PHP_URL_PATH)) {
                '/api/auth/verify-firebase-token' => Http::response(['data' => ['access_token' => 'existing-token']]),
                '/api/v1/legal/me' => Http::response(['data' => ['purchase' => $this->purchaseDocuments]], $this->legalReadStatus),
                '/api/v1/internal/legal/accept' => Http::response(['data' => []], $this->legalAcceptStatus),
                '/api/v1/users/me' => Http::response(['data' => ['id' => '32131864-6d52-4b97-919d-99d4f2abc845']]),
                '/api/v1/plans' => Http::response(['data' => [
                    ['id' => '32131864-6d52-4b97-919d-99d4f2abc846', 'name' => 'Mensile', 'active' => true, 'price' => '12.00', 'currency' => 'EUR', 'billing_period' => 'MONTHLY', 'providers' => [['provider' => 'STRIPE', 'active' => true, 'external_price_id' => 'price_monthly']]],
                    ['name' => 'Solo Apple', 'active' => true, 'providers' => [['provider' => 'APPLE', 'active' => true]]],
                ]]),
                '/api/v1/internal/purchase-eligibility' => Http::response(['data' => ['allowed' => true]]),
                '/api/v1/internal/checkout-sessions' => Http::response(['data' => ['allowed' => true, 'session' => ['id' => 'cs_monthly', 'url' => 'https://checkout.stripe.test/monthly']]]),
                default => Http::response([], 404),
            };
        });
        $this->getJson(route('subscribe.plans'))
            ->assertOk()->assertJsonCount(1, 'plans')->assertJsonPath('plans.0.external_price_id', 'price_monthly')
            ->assertDontSee('Solo Apple')->assertDontSee('existing-token');
        $this->assertDatabaseCount('purchases', 0);
        Http::assertNotSent(fn (Request $request) => str_ends_with($request->url(), '/api/auth/verify-firebase-token'));
        $this->postJson(route('subscribe.checkout'), [...$this->checkoutPayload('price_monthly'), 'recurring_terms_accepted' => true])
            ->assertOk()->assertJson(['redirectUrl' => 'https://checkout.stripe.test/monthly']);
        Http::assertSent(fn (Request $request) => str_ends_with($request->url(), '/api/v1/plans') && ! $request->hasHeader('Authorization') && ! $request->hasHeader('X-Wayout-Signature'));
        Http::assertSent(fn (Request $request) => str_ends_with($request->url(), '/checkout-sessions') && $request['external_price_id'] === 'price_monthly' && ! isset($request['promo_package_code']));
        $this->assertDatabaseHas('purchases', ['external_price_id' => 'price_monthly', 'offer_type' => 'PLAN', 'auto_renew' => true, 'billing_period' => 'MONTHLY', 'amount' => 1200]);
    }

    public function test_recurring_purchase_requires_explicit_renewal_consent(): void
    {
        $this->postJson(route('subscribe.checkout'), $this->checkoutPayload('price_monthly'))
            ->assertUnprocessable()->assertJsonValidationErrors('recurring_terms_accepted');
        Http::assertNotSent(fn (Request $request) => str_ends_with($request->url(), '/checkout-sessions'));
    }

    public function test_confirmation_waits_for_the_selected_offer_then_accepts_a_free_entitlement(): void
    {
        Mail::fake();
        $purchaseId = DatabaseUuid::new();
        $userId = '32131864-6d52-4b97-919d-99d4f2abc845';
        $offerId = '32131864-6d52-4b97-919d-99d4f2abc846';
        DB::table('purchases')->insert([
            'id' => $purchaseId, 'wayout_user_id' => $userId, 'offer_id' => $offerId,
            'offer_type' => 'PROMO_PACKAGE', 'promo_package_code' => 'FREE60', 'plan' => 'FREE60',
            'email' => 'founder@example.com', 'first_name' => 'Matteo', 'last_name' => 'Sportelli',
            'amount' => 0, 'currency' => 'eur', 'status' => 'pending', 'package_duration_days' => 60,
            'created_at' => now(), 'updated_at' => now(),
        ]);
        Http::fake(['*/internal/users/*/subscription' => Http::sequence()
            ->push(['data' => ['has_active_entitlement' => false]])
            ->push(['data' => ['has_active_entitlement' => true, 'offer' => ['id' => 'other-offer'], 'subscription' => ['source' => 'PROMO']]])
            ->push(['data' => ['has_active_entitlement' => true, 'offer' => ['id' => $offerId], 'subscription' => ['source' => 'PROMO', 'start_at' => '2026-11-03T12:00:00Z', 'end_at' => '2027-01-02T12:00:00Z']]])]);
        $statusUrl = URL::temporarySignedRoute('checkout.status', now()->addMinute(), ['purchase' => $purchaseId]);
        $this->getJson($statusUrl)->assertOk()->assertJson(['state' => 'pending']);
        $this->getJson($statusUrl)->assertOk()->assertJson(['state' => 'pending']);
        $this->getJson($statusUrl)->assertOk()->assertJson(['state' => 'confirmed']);
        $this->assertDatabaseHas('purchases', ['id' => $purchaseId, 'status' => 'succeeded', 'package_end_at' => '2027-01-02 12:00:00']);
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
                '/api/v1/legal/me' => Http::response(['data' => ['purchase' => $this->purchaseDocuments]], $this->legalReadStatus),
                '/api/v1/internal/legal/accept' => Http::response(['data' => []], $this->legalAcceptStatus),
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
