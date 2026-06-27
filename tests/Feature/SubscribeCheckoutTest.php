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
            ->assertViewIs('pages.subscribe');
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
        $response = $this->from(route('home'))
            ->post(route('subscribe.access'), [
                'email' => 'Founder@Example.com',
            ]);

        $response->assertRedirect(route('subscribe'))
            ->assertSessionHas('waitlist_offer_access', true)
            ->assertSessionHas('waitlist_email', 'founder@example.com')
            ->assertSessionHas('subscribe_entry_allowed', true);
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
                'plan' => 'creator',
                'direct_checkout' => false,
            ]);

        $response->assertOk()
            ->assertJson(['sessionId' => 'cs_test_123']);

        Http::assertSent(function ($request) {
            $body = $request->data();

            return $request->url() === 'https://api.stripe.com/v1/checkout/sessions'
                && $body['mode'] === 'subscription'
                && $body['line_items[0][price_data][unit_amount]'] === '5900'
                && $body['line_items[0][price_data][product_data][name]'] === 'Founder 12M Creator Pass'
                && $body['line_items[0][price_data][recurring][interval]'] === 'year'
                && $body['customer_email'] === 'founder@example.com'
                && $body['metadata[email]'] === 'founder@example.com';
        });
    }

    public function test_direct_checkout_uses_the_waitlist_email(): void
    {
        $response = $this->withSession([
            'waitlist_offer_access' => true,
            'waitlist_email' => 'founder@example.com',
        ])->postJson(route('subscribe.checkout'), [
            'plan' => 'join',
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
        ]);

        $this->assertDatabaseMissing('purchases', [
            'email' => 'placeholder@example.com',
        ]);
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
                'plan' => 'join',
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
                'plan' => 'join',
                'direct_checkout' => false,
            ]);

        $response->assertStatus(502)
            ->assertJson([
                'error' => 'Checkout temporaneamente non disponibile. Riprova tra qualche minuto.',
            ]);
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
}
