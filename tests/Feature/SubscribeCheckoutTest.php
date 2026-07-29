<?php

namespace Tests\Feature;

use App\Mail\PurchaseConfirmationMail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class SubscribeCheckoutTest extends TestCase
{
    use RefreshDatabase;

    public function test_sales_terms_use_the_canonical_url(): void
    {
        $this->get('/termini-di-vendita')
            ->assertOk()
            ->assertSee('Termini di vendita');

        $this->get('/condizioni-di-vendita')
            ->assertRedirect('/termini-di-vendita')
            ->assertStatus(301);
    }

    public function test_subscribe_page_requires_waitlist_banner_context(): void
    {
        $response = $this->get(route('subscribe'));

        $response->assertRedirect(route('home'));
    }

    public function test_subscribe_page_is_allowed_from_waitlist_banner(): void
    {
        $response = $this->withSession(['subscribe_entry_allowed' => true])
            ->get(route('subscribe'));

        $response->assertOk()
            ->assertViewIs('pages.subscribe')
            ->assertDontSee('Procedendo al pagamento dichiari di aver letto')
            ->assertSee('Confermi di aver letto i')
            ->assertDontSee('href="'.route('legal.passes').'" target="_blank"', false)
            ->assertDontSee('href="'.route('legal.sales').'" target="_blank"', false)
            ->assertDontSee('href="'.route('legal.presale').'" target="_blank"', false)
            ->assertDontSee('href="'.route('legal.refunds').'#recedere" target="_blank"', false)
            ->assertSee('/condizioni-di-pre-sale', false)
            ->assertSee(route('legal.sales'))
            ->assertSee(route('legal.refunds'))
            ->assertDontSee('Versioni documenti:');

        $response->assertSee('1. Che cosa acquisto con un Founder Pass?')
            ->assertSee('26. Dove trovo le condizioni complete e chi posso contattare?')
            ->assertSee('Non è prevista una selezione o approvazione discrezionale specifica')
            ->assertDontSee('Che cos’è il Waitlist Pass?');
        $this->assertSame(26, substr_count($response->getContent(), 'data-legal-faq'));
    }

    public function test_subscribe_plan_comparison_uses_configured_capacities(): void
    {
        DB::table('founder_settings')->updateOrInsert(
            ['key' => 'waitlist_capacity'],
            ['value' => 1234]
        );
        DB::table('founder_settings')->updateOrInsert(
            ['key' => 'join_capacity'],
            ['value' => 734]
        );
        DB::table('founder_settings')->updateOrInsert(
            ['key' => 'creator_capacity'],
            ['value' => 91]
        );

        $response = $this->withSession(['subscribe_entry_allowed' => true])
            ->get(route('subscribe'));

        $response->assertOk()
            ->assertSee('1.234 posti disponibili')
            ->assertSee('734 posti disponibili')
            ->assertSee('91 posti disponibili')
            ->assertDontSee('600 pass')
            ->assertDontSee('200 pass');
    }

    public function test_subscribe_page_cannot_be_opened_directly_after_checkout_was_unlocked(): void
    {
        $response = $this->withSession([
            'waitlist_offer_access' => true,
            'waitlist_email' => 'founder@example.com',
        ])->get(route('subscribe'));

        $response->assertRedirect(route('home'));
    }

    public function test_subscribe_access_stores_the_waitlist_email_in_session(): void
    {
        Mail::fake();

        DB::table('waitlist_entries')->insert([
            'email' => 'founder@example.com',
            'first_name' => 'Ada',
            'last_name' => 'Lovelace',
            'birth_date' => '1990-01-01',
            'phone_prefix' => '+39',
            'phone_number' => '3331234567',
            'offer_shown' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->from(route('home'))
            ->post(route('subscribe.access'), [
                'email' => 'Founder@Example.com',
            ]);

        $response->assertRedirect(route('subscribe'))
            ->assertSessionHas('waitlist_offer_access', true)
            ->assertSessionHas('waitlist_email', 'founder@example.com')
            ->assertSessionHas('subscribe_entry_allowed', true);

        Mail::assertSent(\App\Mail\WaitlistWelcomeMail::class, 1);

        $this->assertDatabaseHas('consent_events', [
            'subject_email' => 'founder@example.com',
            'consent_type' => 'waitlist_legal',
            'action' => 'granted',
            'source' => 'offer_access',
        ]);
    }

    public function test_legacy_waitlist_entry_records_current_legal_versions_on_offer_access_without_checkbox(): void
    {
        Mail::fake();

        DB::table('waitlist_entries')->insert([
            'email' => 'legacy@example.com',
            'first_name' => 'Legacy',
            'last_name' => 'Member',
            'birth_date' => '1990-01-01',
            'phone_prefix' => '+39',
            'phone_number' => '3331234567',
            'offer_shown' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->from(route('home'))
            ->post(route('subscribe.access'), ['email' => 'legacy@example.com'])
            ->assertRedirect(route('subscribe'))
            ->assertSessionHas('waitlist_offer_access', true);

        $this->assertDatabaseHas('consent_events', [
            'subject_email' => 'legacy@example.com',
            'consent_type' => 'waitlist_legal',
            'action' => 'granted',
            'source' => 'offer_access',
        ]);
    }

    public function test_checkout_uses_the_selected_founder_plan(): void
    {
        config()->set('services.stripe.secret', 'sk_test_123');

        Http::fake([
            'https://api.stripe.com/v1/checkout/sessions' => Http::response(['id' => 'cs_test_123'], 200),
        ]);

        $response = $this->withSession([
            'waitlist_offer_access' => true,
            'waitlist_email' => 'founder@example.com',
        ])
            ->postJson(route('subscribe.checkout'), [
                ...$this->customerPayload(),
                'invoice_requested' => true,
                'fiscal_code' => 'abcxyz90a01f205z',
                'plan' => 'creator',
                'direct_checkout' => false,
            ]);

        $response->assertOk()
            ->assertJson(['sessionId' => 'cs_test_123']);

        Http::assertSent(function ($request) {
            $body = $request->data();

            return $request->url() === 'https://api.stripe.com/v1/checkout/sessions'
                && $body['mode'] === 'payment'
                && $body['line_items[0][price_data][unit_amount]'] === '5900'
                && $body['line_items[0][price_data][product_data][name]'] === 'Founder 12M Creator Pass'
                && $body['customer_email'] === 'founder@example.com'
                && $body['metadata[email]'] === 'founder@example.com'
                && $body['metadata[phone_prefix]'] === '+39'
                && $body['metadata[phone_number]'] === '3331234567'
                && filled($body['metadata[purchase_id]'] ?? null)
                && $body['metadata[fiscal_code]'] === 'ABCXYZ90A01F205Z';
        });

        $this->assertDatabaseHas('purchases', [
            'email' => 'founder@example.com',
            'plan' => 'creator',
            'status' => 'pending',
            'stripe_session_id' => 'cs_test_123',
        ]);

        $this->assertDatabaseMissing('consent_events', [
            'subject_email' => 'founder@example.com',
            'consent_type' => 'purchase_legal',
        ]);
    }

    public function test_stripe_checkout_reserves_the_last_founder_pass_before_payment(): void
    {
        config()->set('services.stripe.secret', 'sk_test_123');

        DB::table('founder_settings')->updateOrInsert(
            ['key' => 'join_capacity'],
            ['value' => 1, 'created_at' => now(), 'updated_at' => now()]
        );

        DB::table('purchases')->insert([
            'email' => 'reserved@example.com',
            'plan' => 'join',
            'amount' => 2900,
            'currency' => 'eur',
            'stripe_session_id' => 'cs_reserved',
            'status' => 'pending',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        Http::fake([
            'https://api.stripe.com/v1/checkout/sessions' => Http::response(['id' => 'cs_should_not_be_created'], 200),
        ]);

        $response = $this->withSession([
            'waitlist_offer_access' => true,
            'waitlist_email' => 'founder@example.com',
        ])->postJson(route('subscribe.checkout'), [
            ...$this->customerPayload(),
            'plan' => 'join',
            'direct_checkout' => false,
        ]);

        $response->assertUnprocessable()
            ->assertJson([
                'error' => 'Founder Join 12M Pass è esaurito. Scegli un altro pass o resta in waitlist.',
            ]);

        Http::assertNothingSent();
    }

    public function test_expired_stripe_reservations_do_not_block_a_founder_pass(): void
    {
        config()->set('services.stripe.secret', 'sk_test_123');

        DB::table('founder_settings')->updateOrInsert(
            ['key' => 'join_capacity'],
            ['value' => 1, 'created_at' => now(), 'updated_at' => now()]
        );

        DB::table('purchases')->insert([
            'email' => 'expired@example.com',
            'plan' => 'join',
            'amount' => 2900,
            'currency' => 'eur',
            'stripe_session_id' => 'cs_expired',
            'status' => 'pending',
            'created_at' => now()->subMinutes(16),
            'updated_at' => now()->subMinutes(16),
        ]);

        Http::fake([
            'https://api.stripe.com/v1/checkout/sessions' => Http::response(['id' => 'cs_new'], 200),
        ]);

        $response = $this->withSession([
            'waitlist_offer_access' => true,
            'waitlist_email' => 'founder@example.com',
        ])->postJson(route('subscribe.checkout'), [
            ...$this->customerPayload(),
            'plan' => 'join',
            'direct_checkout' => false,
        ]);

        $response->assertOk()
            ->assertJson(['sessionId' => 'cs_new']);

        $this->assertDatabaseHas('purchases', [
            'email' => 'founder@example.com',
            'plan' => 'join',
            'status' => 'pending',
            'stripe_session_id' => 'cs_new',
        ]);

        $this->assertDatabaseHas('purchases', [
            'email' => 'expired@example.com',
            'stripe_session_id' => 'cs_expired',
            'status' => 'expired',
        ]);
    }

    public function test_retrying_stripe_checkout_reuses_the_existing_pending_purchase(): void
    {
        config()->set('services.stripe.secret', 'sk_test_123');

        $originalCreatedAt = now()->subMinutes(5)->startOfSecond();

        $purchaseId = DB::table('purchases')->insertGetId([
            'email' => 'founder@example.com',
            'plan' => 'join',
            'amount' => 2900,
            'currency' => 'eur',
            'stripe_session_id' => 'cs_previous',
            'status' => 'pending',
            'created_at' => $originalCreatedAt,
            'updated_at' => $originalCreatedAt,
        ]);

        Http::fake([
            'https://api.stripe.com/v1/checkout/sessions' => Http::response(['id' => 'cs_retried'], 200),
        ]);

        $response = $this->withSession([
            'waitlist_offer_access' => true,
            'waitlist_email' => 'founder@example.com',
        ])->postJson(route('subscribe.checkout'), [
            ...$this->customerPayload(),
            'plan' => 'join',
            'direct_checkout' => false,
        ]);

        $response->assertOk()
            ->assertJson(['sessionId' => 'cs_retried']);

        $this->assertSame(1, DB::table('purchases')
            ->where('email', 'founder@example.com')
            ->where('plan', 'join')
            ->count());

        $this->assertDatabaseHas('purchases', [
            'id' => $purchaseId,
            'status' => 'pending',
            'stripe_session_id' => 'cs_retried',
            'created_at' => $originalCreatedAt,
        ]);


        $this->assertDatabaseMissing('consent_events', [
            'purchase_id' => $purchaseId,
            'consent_type' => 'purchase_legal',
        ]);
    }

    public function test_direct_checkout_uses_the_waitlist_email(): void
    {
        Mail::fake();

        $response = $this->withSession([
            'waitlist_offer_access' => true,
            'waitlist_email' => 'founder@example.com',
        ])->postJson(route('subscribe.checkout'), [
            ...$this->customerPayload(),
            'invoice_requested' => true,
            'fiscal_code' => 'abcxyz90a01f205z',
            'plan' => 'join',
            'direct_checkout' => true,
        ]);

        $response->assertOk()
            ->assertJson([
                'completed' => true,
                'redirectUrl' => route('checkout.success'),
            ]);

        $this->assertDatabaseHas('purchases', [
            'email' => 'founder@example.com',
            'plan' => 'join',
            'amount' => 2900,
            'status' => 'succeeded',
            'phone_prefix' => '+39',
            'phone_number' => '3331234567',
            'invoice_requested' => true,
            'fiscal_code' => 'ABCXYZ90A01F205Z',
        ]);

        $this->assertDatabaseMissing('purchases', [
            'email' => 'placeholder@example.com',
        ]);

        Mail::assertSent(PurchaseConfirmationMail::class, fn (PurchaseConfirmationMail $mail) =>
            $mail->hasTo('founder@example.com') && $mail->purchase['plan_name'] === 'Founder Join 12M Pass'
        );

        $this->assertNotNull(DB::table('purchases')
            ->where('email', 'founder@example.com')
            ->value('confirmation_email_sent_at'));

        $purchaseId = DB::table('purchases')->where('email', 'founder@example.com')->value('id');
        $this->assertDatabaseHas('consent_events', [
            'purchase_id' => $purchaseId,
            'subject_email' => 'founder@example.com',
            'consent_type' => 'purchase_legal',
            'action' => 'granted',
            'source' => 'direct_checkout',
        ]);

        $purchaseConsent = DB::table('consent_events')->where('purchase_id', $purchaseId)->first();
        $this->assertArrayHasKey('purchase_acceptance', json_decode($purchaseConsent->document_versions, true));
    }

    public function test_checkout_requires_waitlist_email_in_session(): void
    {
        $response = $this->withSession(['waitlist_offer_access' => true])
            ->postJson(route('subscribe.checkout'), [
                'plan' => 'join',
            ]);

        $response->assertForbidden()
            ->assertJson([
                'error' => 'Email waitlist non trovata. Reinserisci la tua email dalla home.',
            ]);
    }

    public function test_checkout_rejects_unknown_plan(): void
    {
        $response = $this->withSession([
            'waitlist_offer_access' => true,
            'waitlist_email' => 'founder@example.com',
        ])
            ->postJson(route('subscribe.checkout'), [
                'plan' => 'enterprise',
            ]);

        $response->assertUnprocessable()
            ->assertJson([
                'error' => 'Seleziona un Founder Pass valido.',
            ]);
    }

    public function test_direct_checkout_database_failure_returns_a_user_friendly_error(): void
    {
        Schema::dropIfExists('purchases');

        $response = $this->withSession([
            'waitlist_offer_access' => true,
            'waitlist_email' => 'founder@example.com',
        ])
            ->postJson(route('subscribe.checkout'), [
                ...$this->customerPayload(),
                'plan' => 'join',
                'direct_checkout' => true,
            ]);

        $response->assertStatus(500)
            ->assertJson([
                'error' => 'Non siamo riusciti a registrare il pagamento. Riprova tra qualche minuto.',
            ]);
    }

    public function test_stripe_error_is_not_exposed_to_the_browser(): void
    {
        config()->set('services.stripe.secret', 'sk_test_123');

        Http::fake([
            'https://api.stripe.com/v1/checkout/sessions' => Http::response('secret stripe details', 500),
        ]);

        $response = $this->withSession([
            'waitlist_offer_access' => true,
            'waitlist_email' => 'founder@example.com',
        ])
            ->postJson(route('subscribe.checkout'), [
                ...$this->customerPayload(),
                'plan' => 'join',
                'direct_checkout' => false,
            ]);

        $response->assertStatus(502)
            ->assertJson([
                'error' => 'Checkout temporaneamente non disponibile. Riprova tra qualche minuto.',
            ])
            ->assertDontSee('secret stripe details');
    }

    public function test_stripe_response_without_session_id_is_rejected(): void
    {
        config()->set('services.stripe.secret', 'sk_test_123');

        Http::fake([
            'https://api.stripe.com/v1/checkout/sessions' => Http::response(['ok' => true], 200),
        ]);

        $response = $this->withSession([
            'waitlist_offer_access' => true,
            'waitlist_email' => 'founder@example.com',
        ])
            ->postJson(route('subscribe.checkout'), [
                ...$this->customerPayload(),
                'plan' => 'join',
                'direct_checkout' => false,
            ]);

        $response->assertStatus(502)
            ->assertJson([
                'error' => 'Checkout temporaneamente non disponibile. Riprova tra qualche minuto.',
            ]);
    }

    public function test_successful_stripe_callback_confirms_the_reserved_purchase(): void
    {
        Mail::fake();
        config()->set('services.stripe.secret', 'sk_test_123');

        $purchaseId = DB::table('purchases')->insertGetId([
            'email' => 'founder@example.com',
            'plan' => 'join',
            'amount' => 2900,
            'currency' => 'eur',
            'stripe_session_id' => 'cs_test_paid',
            'status' => 'pending',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        Http::fake([
            'https://api.stripe.com/v1/checkout/sessions/cs_test_paid' => Http::response($this->paidStripeSession($purchaseId), 200),
        ]);

        $response = $this->get(route('checkout.success', ['session_id' => 'cs_test_paid']));

        $response->assertOk()
            ->assertSee('Documenti relativi all’acquisto')
            ->assertSee('href="'.route('legal.passes').'" target="_blank"', false)
            ->assertSee('href="'.route('legal.sales').'" target="_blank"', false)
            ->assertSee('href="'.route('legal.presale').'" target="_blank"', false)
            ->assertSee('href="'.route('legal.refunds').'#recedere" target="_blank"', false)
            ->assertSee('Recedere dal contratto');

        $this->assertDatabaseHas('purchases', [
            'id' => $purchaseId,
            'status' => 'succeeded',
            'stripe_session_id' => 'cs_test_paid',
            'fiscal_code' => 'ABCXYZ90A01F205Z',
        ]);

        $this->assertDatabaseHas('consent_events', [
            'purchase_id' => $purchaseId,
            'subject_email' => 'founder@example.com',
            'consent_type' => 'purchase_legal',
            'action' => 'granted',
            'source' => 'stripe_payment_success',
        ]);

        Mail::assertSent(PurchaseConfirmationMail::class, 1);

        $this->get(route('checkout.success', ['session_id' => 'cs_test_paid']))->assertOk();
        Mail::assertSent(PurchaseConfirmationMail::class, 1);
        $this->assertSame(1, DB::table('consent_events')
            ->where('purchase_id', $purchaseId)
            ->where('consent_type', 'purchase_legal')
            ->count());
    }

    public function test_successful_stripe_callback_does_not_overbook_when_capacity_is_gone(): void
    {
        config()->set('services.stripe.secret', 'sk_test_123');

        DB::table('founder_settings')->updateOrInsert(
            ['key' => 'join_capacity'],
            ['value' => 1, 'created_at' => now(), 'updated_at' => now()]
        );

        DB::table('purchases')->insert([
            'email' => 'winner@example.com',
            'plan' => 'join',
            'amount' => 2900,
            'currency' => 'eur',
            'stripe_session_id' => 'cs_winner',
            'status' => 'succeeded',
            'created_at' => now()->subMinute(),
            'updated_at' => now()->subMinute(),
        ]);

        $purchaseId = DB::table('purchases')->insertGetId([
            'email' => 'late@example.com',
            'plan' => 'join',
            'amount' => 2900,
            'currency' => 'eur',
            'stripe_session_id' => 'cs_late',
            'status' => 'pending',
            'created_at' => now()->subMinutes(20),
            'updated_at' => now()->subMinutes(20),
        ]);

        Http::fake([
            'https://api.stripe.com/v1/checkout/sessions/cs_late' => Http::response($this->paidStripeSession($purchaseId, 'late@example.com'), 200),
        ]);

        $response = $this->get(route('checkout.success', ['session_id' => 'cs_late']));

        $response->assertOk();

        $this->assertDatabaseHas('purchases', [
            'id' => $purchaseId,
            'status' => 'overbooked',
            'stripe_session_id' => 'cs_late',
        ]);

        $this->assertDatabaseMissing('consent_events', [
            'purchase_id' => $purchaseId,
            'consent_type' => 'purchase_legal',
        ]);

        $this->assertSame(1, DB::table('purchases')
            ->where('plan', 'join')
            ->where('status', 'succeeded')
            ->count());
    }

    public function test_purchase_confirmation_email_can_be_requested_again(): void
    {
        Mail::fake();

        DB::table('purchases')->insert([
            'email' => 'buyer@example.com',
            'plan' => 'creator',
            'amount' => 5900,
            'currency' => 'eur',
            'stripe_session_id' => 'direct_test',
            'status' => 'succeeded',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->from(route('home'))
            ->withSession(['waitlist_email' => 'buyer@example.com'])
            ->post(route('purchase.confirmation.resend'));

        $response->assertRedirect(route('home'))
            ->assertSessionHas('purchase_confirmation_success', 'Ti abbiamo inviato nuovamente l’email di conferma acquisto.')
            ->assertSessionHas('purchased_plan.name', 'Founder 12M Creator Pass');

        Mail::assertSent(PurchaseConfirmationMail::class, function (PurchaseConfirmationMail $mail) {
            return $mail->hasTo('buyer@example.com')
                && $mail->purchase['plan_name'] === 'Founder 12M Creator Pass'
                && $mail->purchase['amount'] === 5900;
        });
    }

    public function test_repeated_purchase_confirmation_requests_are_blocked_during_cooldown(): void
    {
        Mail::fake();
        config()->set('email.resend_cooldown_seconds', 300);
        $this->travelTo(now()->startOfSecond());

        DB::table('purchases')->insert([
            'email' => 'buyer@example.com',
            'plan' => 'join',
            'amount' => 2900,
            'currency' => 'eur',
            'stripe_session_id' => 'direct_cooldown',
            'status' => 'succeeded',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $session = ['waitlist_email' => 'buyer@example.com'];

        $this->from(route('home'))
            ->withSession($session)
            ->post(route('purchase.confirmation.resend'))
            ->assertSessionHas('purchase_confirmation_success');

        $this->from(route('home'))
            ->withSession($session)
            ->post(route('purchase.confirmation.resend'))
            ->assertSessionHas(
                'purchase_confirmation_error',
                'Email già inviata. Attendi 300 secondi prima di richiederne un’altra.',
            );

        Mail::assertSent(PurchaseConfirmationMail::class, 1);

        $this->travel(301)->seconds();

        $this->from(route('home'))
            ->withSession($session)
            ->post(route('purchase.confirmation.resend'))
            ->assertSessionHas('purchase_confirmation_success');

        Mail::assertSent(PurchaseConfirmationMail::class, 2);
        $this->travelBack();
    }

    public function test_purchase_confirmation_resend_requires_a_confirmed_purchase(): void
    {
        Mail::fake();

        $response = $this->from(route('home'))
            ->withSession(['waitlist_email' => 'buyer@example.com'])
            ->post(route('purchase.confirmation.resend'));

        $response->assertRedirect(route('home'))
            ->assertSessionHas('purchase_confirmation_error', 'Non risulta ancora un acquisto confermato per questa email.');

        Mail::assertNothingSent();
    }

    public function test_purchase_confirmation_resend_database_failure_returns_a_user_friendly_error(): void
    {
        Mail::fake();
        Schema::dropIfExists('purchases');

        $response = $this->from(route('home'))
            ->withSession(['waitlist_email' => 'buyer@example.com'])
            ->post(route('purchase.confirmation.resend'));

        $response->assertRedirect(route('home'))
            ->assertSessionHas('purchase_confirmation_error', 'Non siamo riusciti a inviare nuovamente l’email. Riprova tra qualche minuto.');

        Mail::assertNothingSent();
    }

    private function customerPayload(): array
    {
        return [
            'first_name' => 'Ada',
            'last_name' => 'Lovelace',
            'birth_date' => '1990-01-01',
            'phone_prefix' => '+39',
            'phone_number' => '3331234567',
            'purchase_terms_accepted' => true,
        ];
    }

    private function paidStripeSession(int $purchaseId, string $email = 'founder@example.com'): array
    {
        return [
            'payment_status' => 'paid',
            'amount_total' => 2900,
            'currency' => 'eur',
            'customer_email' => $email,
            'metadata' => [
                'purchase_id' => (string) $purchaseId,
                'email' => $email,
                'first_name' => 'Ada',
                'last_name' => 'Lovelace',
                'birth_date' => '1990-01-01',
                'phone_prefix' => '+39',
                'phone_number' => '3331234567',
                'plan' => 'join',
                'invoice_requested' => 'true',
                'fiscal_code' => 'abcxyz90a01f205z',
            ],
        ];
    }
}
