<?php

namespace Tests\Feature;

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
            'analytics.meta_capi.access_token' => 'server-secret',
            'analytics.meta_capi.api_version' => 'v23.0',
        ]);
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
