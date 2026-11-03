<?php

namespace Tests\Feature;

use App\Http\Controllers\WaitlistController;
use App\Mail\WaitlistVerificationMail;
use App\Support\DatabaseUuid;
use App\Support\MetaConversions;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Factory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class MetaConversionsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config()->set([
            'analytics.enabled' => true,
            'analytics.consent_version' => 2,
            'analytics.meta_pixel_id' => '1234567890',
            'analytics.meta_capi.enabled' => true,
            'analytics.meta_capi.context_store' => 'array',
            'analytics.meta_capi.access_token' => 'server-secret',
            'analytics.meta_capi.api_version' => 'v23.0',
        ]);
        // app_live retains the controller but intentionally has no public waitlist routes.
        if (! Route::has('waitlist.store')) {
            Route::middleware('web')->group(function (): void {
                Route::post('/tracking-test/waitlist', [WaitlistController::class, 'store'])->name('waitlist.store');
                Route::get('/tracking-test/verify/{token}', [WaitlistController::class, 'showVerification'])->name('waitlist.verify.show');
                Route::post('/tracking-test/verify/{token}', [WaitlistController::class, 'verify'])->name('waitlist.verify');
            });
            Route::getRoutes()->refreshNameLookups();
        }
        Http::preventStrayRequests();
        Http::fake(['graph.facebook.com/*' => Http::response(['events_received' => 1])]);
    }

    public function test_waitlist_verification_connects_server_event_to_the_pixel_id_on_the_return_page(): void
    {
        Mail::fake();
        $browser = $this->consentingRequest();
        $this->withUnencryptedCookies($browser->cookies->all())->post(route('waitlist.store'), [
            'email' => 'member@example.com', 'waitlist_terms_accepted' => '1',
        ])->assertSessionHas('waitlist_verification_sent', true);
        $this->assertDatabaseCount('meta_conversion_events', 0);
        $mail = Mail::sent(WaitlistVerificationMail::class)->first();
        $token = basename(parse_url($mail->verificationUrl, PHP_URL_PATH));
        $this->post(route('waitlist.verify', ['token' => $token]))->assertRedirect(route('home'));
        $eventId = DB::table('meta_conversion_events')->value('event_id');
        $this->assertNotNull($eventId);
        $this->get(route('home'))->assertOk()->assertSee('data-analytics-event-id="'.$eventId.'"', false);
        $this->assertDatabaseCount('meta_conversion_events', 1);
    }

    public function test_verified_lead_is_encrypted_deduplicated_and_sent_with_hashed_email(): void
    {
        $entry = $this->createVerifiedWaitlistEntry('member@example.com');
        $request = $this->consentingRequest();
        $meta = app(MetaConversions::class);
        $meta->lead($request, $entry);
        $meta->lead($request, $entry);
        $this->assertDatabaseCount('meta_conversion_events', 1);
        $event = DB::table('meta_conversion_events')->first();
        $this->assertStringNotContainsString('member@example.com', $event->payload);
        $this->assertStringNotContainsString('203.0.113.42', $event->payload);
        Http::assertNothingSent();
        $this->assertSame(0, $meta->sendPending());
        Http::assertSent(fn ($request) => $request->hasHeader('Authorization', 'Bearer server-secret')
            && $request['data'][0]['user_data']['em'] === [hash('sha256', 'member@example.com')]
            && $request['data'][0]['user_data']['fbp'] === 'fb.1.1234567890123.12345'
            && $request['data'][0]['event_id'] === MetaConversions::eventId('Lead', $entry->id)
            && $request['data'][0]['event_name'] === 'Lead'
            && $request['data'][0]['action_source'] === 'website'
            && ! str_contains($request['data'][0]['event_source_url'], 'secret'));
        $this->assertNull(DB::table('meta_conversion_events')->value('payload'));
        $this->assertNotNull(DB::table('meta_conversion_events')->value('delivered_at'));
        $meta->lead($request, $entry);
        $meta->sendPending();
        Http::assertSentCount(1);
    }

    public function test_missing_refused_old_or_unaudited_cookie_consent_does_not_record_a_lead(): void
    {
        $entry = $this->createVerifiedWaitlistEntry();
        $meta = app(MetaConversions::class);
        $meta->lead(Request::create('/'), $entry);
        $meta->lead($this->consentingRequest(marketing: false), $entry);
        $meta->lead($this->consentingRequest(version: 1), $entry);
        $meta->lead($this->consentingRequest(audit: false), $entry);
        $this->assertDatabaseCount('meta_conversion_events', 0);
        $meta->sendPending();
        Http::assertNothingSent();
    }

    public function test_disabled_capi_does_not_capture_or_send_even_with_marketing_consent(): void
    {
        config()->set('analytics.meta_capi.enabled', false);
        app(MetaConversions::class)->lead($this->consentingRequest(), $this->createVerifiedWaitlistEntry());
        $this->assertDatabaseCount('meta_conversion_events', 0);
        app(MetaConversions::class)->sendPending();
        Http::assertNothingSent();
    }

    public function test_revocation_before_delivery_discards_the_payload(): void
    {
        $request = $this->consentingRequest();
        app(MetaConversions::class)->lead($request, $this->createVerifiedWaitlistEntry());
        $consent = json_decode($request->cookie('wayout_cookie_consent'), true);
        $this->travel(1)->seconds();
        $this->postJson(route('cookie-consent.store'), [
            'consent_id' => $consent['consentId'], 'consent_version' => 2, 'analytics' => true, 'marketing' => false,
        ])->assertNoContent();
        app(MetaConversions::class)->sendPending();
        Http::assertNothingSent();
        $this->assertNull(DB::table('meta_conversion_events')->value('payload'));
        $this->assertNull(DB::table('meta_conversion_events')->value('delivered_at'));
    }

    public function test_failed_delivery_retries_with_same_id_and_original_event_time(): void
    {
        Http::swap(new Factory);
        Http::preventStrayRequests();
        Http::fake(['graph.facebook.com/*' => Http::sequence()
            ->push(['error' => ['code' => 2]], 500)
            ->push(['events_received' => 1])]);
        app(MetaConversions::class)->lead($this->consentingRequest(), $this->createVerifiedWaitlistEntry());
        $original = json_decode(Crypt::decryptString(DB::table('meta_conversion_events')->value('payload')), true);
        $this->assertSame(1, app(MetaConversions::class)->sendPending());
        app(MetaConversions::class)->sendPending();
        Http::assertSentCount(1);
        $this->travel(2)->minutes();
        $this->assertSame(0, app(MetaConversions::class)->sendPending());
        Http::assertSentCount(2);
        foreach (Http::recorded() as [$request]) {
            $this->assertSame($original, $request['data'][0]);
        }
    }

    public function test_purchase_requires_confirmed_status_and_uses_saved_checkout_consent(): void
    {
        $id = DatabaseUuid::new();
        DB::table('purchases')->insert([
            'id' => $id, 'email' => 'buyer@example.com', 'plan' => 'join', 'amount' => 2990,
            'currency' => 'eur', 'status' => 'pending', 'order_reference' => 'WO-TEST',
            'created_at' => now(), 'updated_at' => now(),
        ]);
        $meta = app(MetaConversions::class);
        $meta->rememberCheckout($this->consentingRequest(), DB::table('purchases')->first());
        $meta->purchase(DB::table('purchases')->first());
        $this->assertDatabaseCount('meta_conversion_events', 0);
        DB::table('purchases')->where('id', $id)->update(['status' => 'succeeded']);
        // The scheduled sender can process a confirmed order without a browser request.
        $meta->sendPending();
        Http::assertSent(fn ($request) => $request['data'][0]['event_name'] === 'Purchase'
            && $request['data'][0]['custom_data'] === ['value' => 29.9, 'currency' => 'EUR']
            && $request['data'][0]['event_id'] === MetaConversions::eventId('Purchase', 'WO-TEST'));
        $this->assertNull(DB::table('purchases')->value('meta_conversion_context'));
    }

    public function test_expired_events_are_removed_without_delivery(): void
    {
        app(MetaConversions::class)->lead($this->consentingRequest(), $this->createVerifiedWaitlistEntry());
        $this->travel(8)->days();
        app(MetaConversions::class)->sendPending();
        $this->assertDatabaseCount('meta_conversion_events', 0);
        Http::assertNothingSent();
    }

    public function test_real_browser_cookies_reach_the_server_without_laravel_encryption(): void
    {
        $browser = $this->consentingRequest();
        $context = null;
        Route::middleware('web')->get('/meta-cookie-test', function (Request $request) use (&$context) {
            $context = app(MetaConversions::class)->capture($request, 'member@example.com');

            return response()->noContent();
        });
        $this->withUnencryptedCookies($browser->cookies->all())->get('/meta-cookie-test')->assertNoContent();
        $this->assertNotNull($context);
    }

    public function test_fbc_cookie_and_genuine_click_fallback_reach_meta_unchanged(): void
    {
        $this->freezeTime();
        $meta = app(MetaConversions::class);
        $request = $this->consentingRequest();
        $request->cookies->set('_fbc', 'fb.1.1780000000123.Click_ID-123');
        $context = json_decode(Crypt::decryptString($meta->capture($request, 'member@example.com')), true);
        $this->assertSame('fb.1.1780000000123.Click_ID-123', $context['user_data']['fbc']);
        $request->query->set('fbclid', 'New_Click-456');
        $context = json_decode(Crypt::decryptString($meta->capture($request, 'member@example.com')), true);
        $this->assertSame('fb.1.'.now()->getTimestampMs().'.New_Click-456', $context['user_data']['fbc']);
        $meta->lead($request, $this->createVerifiedWaitlistEntry());
        $meta->sendPending();
        Http::assertSent(fn ($request) => str_ends_with($request['data'][0]['user_data']['fbc'], '.New_Click-456'));
    }

    public function test_organic_traffic_and_invalid_click_ids_do_not_produce_fbc(): void
    {
        $request = $this->consentingRequest();
        foreach ([null, 'bad.click', ['array'], str_repeat('x', 451)] as $clickId) {
            $request->query->set('fbclid', $clickId);
            $context = json_decode(Crypt::decryptString(app(MetaConversions::class)->capture($request, 'member@example.com')), true);
            $this->assertArrayNotHasKey('fbc', $context['user_data']);
        }
    }

    public function test_saved_context_preserves_click_and_consent_across_browsers(): void
    {
        $this->freezeTime();
        $request = $this->consentingRequest();
        $request->cookies->set('_fbc', 'fb.1.1780000000123.Click_ID-123');
        $meta = app(MetaConversions::class);
        $saved = $meta->capture($request, 'member@example.com');
        $entry = $this->createVerifiedWaitlistEntry();
        $this->assertSame(MetaConversions::eventId('Lead', $entry->id), $meta->lead(Request::create('/'), $entry, $saved));
        $meta->sendPending();
        Http::assertSent(fn ($request) => $request['data'][0]['user_data']['fbc'] === 'fb.1.1780000000123.Click_ID-123'
            && $request['data'][0]['user_data']['client_user_agent'] === 'Test browser');
    }

    public function test_saved_context_cannot_override_expired_or_revoked_consent_or_explicit_refusal(): void
    {
        $meta = app(MetaConversions::class);
        $request = $this->consentingRequest();
        $saved = $meta->capture($request, 'member@example.com');
        $entry = $this->createVerifiedWaitlistEntry();
        $refusal = Request::create('/', cookies: ['wayout_cookie_consent' => json_encode(['marketing' => false])]);
        $this->assertNull($meta->lead($refusal, $entry, $saved));
        $consent = json_decode($request->cookie('wayout_cookie_consent'), true);
        DB::table('cookie_consent_events')->where('consent_id', $consent['consentId'])->update(['marketing' => false]);
        $this->assertNull($meta->lead(Request::create('/'), $entry, $saved));
        DB::table('cookie_consent_events')->where('consent_id', $consent['consentId'])->update([
            'marketing' => true, 'occurred_at' => now()->subDays(181),
        ]);
        $this->assertNull($meta->lead(Request::create('/'), $entry, $saved));
        $this->assertDatabaseCount('meta_conversion_events', 0);
    }

    public function test_verification_recovers_initial_context_without_cookies_and_emits_matching_pixel_id(): void
    {
        Mail::fake();
        $browser = $this->consentingRequest();
        $browser->cookies->set('_fbc', 'fb.1.1780000000123.Click_ID-123');
        $this->withUnencryptedCookies($browser->cookies->all())->post(route('waitlist.store'), [
            'email' => 'member@example.com', 'waitlist_terms_accepted' => '1',
        ])->assertSessionHas('waitlist_verification_sent', true);
        $tokenHash = DB::table('waitlist_email_verifications')->value('token_hash');
        $saved = app(MetaConversions::class)->verificationContext($tokenHash);
        $this->assertNotNull($saved);
        $this->assertStringNotContainsString('Click_ID-123', $saved);
        $token = basename(parse_url(Mail::sent(WaitlistVerificationMail::class)->first()->verificationUrl, PHP_URL_PATH));
        $this->defaultCookies = [];
        $this->unencryptedCookies = [];
        $this->post(route('waitlist.verify', ['token' => $token]))->assertRedirect(route('home'));
        $eventId = DB::table('meta_conversion_events')->value('event_id');
        $this->assertNotNull($eventId);
        $this->assertNull(app(MetaConversions::class)->verificationContext($tokenHash));
        $this->get(route('home'))->assertSee('data-analytics-event-id="'.$eventId.'"', false);
        $this->get(route('home'))->assertDontSee('data-analytics-page-event="waitlist_email_verified"', false);
        app(MetaConversions::class)->sendPending();
        Http::assertSent(fn ($request) => $request['data'][0]['user_data']['fbc'] === 'fb.1.1780000000123.Click_ID-123');
        $this->post(route('waitlist.verify', ['token' => $token]));
        $this->assertDatabaseCount('meta_conversion_events', 1);
    }

    public function test_verification_attribution_expires_without_database_changes(): void
    {
        $meta = app(MetaConversions::class);
        $meta->rememberVerification($this->consentingRequest(), 'member@example.com', 'token-hash', now()->addMinute()->timestamp);
        $this->assertNotNull($meta->verificationContext('token-hash'));
        $this->travel(2)->minutes();
        $this->assertNull($meta->verificationContext('token-hash'));
        $this->assertDatabaseCount('meta_conversion_events', 0);
        Http::assertNothingSent();
    }

    public function test_status_is_read_only_and_does_not_send_pending_events(): void
    {
        app(MetaConversions::class)->lead($this->consentingRequest(), $this->createVerifiedWaitlistEntry());
        $before = DB::table('meta_conversion_events')->first();
        $this->artisan('meta:status')->expectsOutputToContain('CAPI configurata: sì')->assertSuccessful();
        $this->assertEquals($before, DB::table('meta_conversion_events')->first());
        Http::assertNothingSent();
    }

    public function test_late_marketing_consent_covers_the_pixel_lead_without_accepting_arbitrary_events(): void
    {
        $entry = $this->createVerifiedWaitlistEntry();
        $id = MetaConversions::eventId('Lead', $entry->id);
        $this->postJson(route('analytics.lead'), ['event_id' => $id])->assertForbidden();
        $this->withSession(['waitlist_verified_entry_id' => $entry->id, 'meta_verified_lead_id' => $id]);
        $this->postJson(route('analytics.lead'), ['event_id' => $id])->assertNoContent();
        $this->assertDatabaseCount('meta_conversion_events', 0);
        $browser = $this->consentingRequest();
        $browser->cookies->set('_fbc', 'fb.1.1780000000123.Real_Click');
        $this->withCredentials()->withUnencryptedCookies($browser->cookies->all());
        $this->postJson(route('analytics.lead'), ['event_id' => 'lead_wrong'])->assertForbidden();
        $this->postJson(route('analytics.lead'), ['event_id' => $id])->assertNoContent();
        $this->postJson(route('analytics.lead'), ['event_id' => $id])->assertNoContent();
        $this->assertDatabaseCount('meta_conversion_events', 1);
        app(MetaConversions::class)->sendPending();
        Http::assertSent(fn ($request) => $request['data'][0]['event_id'] === $id
            && $request['data'][0]['user_data']['fbc'] === 'fb.1.1780000000123.Real_Click');
    }

    public function test_confirming_an_already_verified_address_does_not_emit_another_pixel_lead(): void
    {
        Mail::fake();
        $this->createVerifiedWaitlistEntry();
        $browser = $this->consentingRequest();
        $this->withUnencryptedCookies($browser->cookies->all())->post(route('waitlist.store'), [
            'email' => 'member@example.com', 'waitlist_terms_accepted' => '1',
        ])->assertSessionHas('waitlist_verification_sent', true);
        $token = basename(parse_url(Mail::sent(WaitlistVerificationMail::class)->first()->verificationUrl, PHP_URL_PATH));
        $this->post(route('waitlist.verify', ['token' => $token]))->assertRedirect(route('home'));
        $this->get(route('home'))->assertDontSee('data-analytics-page-event="waitlist_email_verified"', false);
        $this->assertDatabaseCount('meta_conversion_events', 0);
    }

    private function consentingRequest(bool $marketing = true, int $version = 2, bool $audit = true): Request
    {
        $consentId = DatabaseUuid::new();
        if ($audit) {
            DB::table('cookie_consent_events')->insert([
                'id' => DatabaseUuid::new(), 'consent_id' => $consentId, 'consent_version' => $version,
                'analytics' => false, 'marketing' => $marketing, 'occurred_at' => now(),
            ]);
        }

        return Request::create('/waitlist/verify/secret', 'POST', cookies: [
            'wayout_cookie_consent' => json_encode(['consentId' => $consentId, 'version' => $version, 'marketing' => $marketing, 'analytics' => false]),
            '_fbp' => 'fb.1.1234567890123.12345',
        ], server: ['REMOTE_ADDR' => '203.0.113.42', 'HTTP_USER_AGENT' => 'Test browser']);
    }
}
