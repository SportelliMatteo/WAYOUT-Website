<?php

namespace Tests\Feature;

use App\Support\DatabaseUuid;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class PurchaseSupportTest extends TestCase
{
    use RefreshDatabase;

    private const TICKET = '41a826f7-dbbe-4c82-874c-436c6cfe7f2a';

    private int $backendStatus = 201;

    private bool $malformedResponse = false;

    protected function setUp(): void
    {
        parent::setUp();
        Http::fake(['*/internal/support/tickets' => fn () => Http::response(
            $this->malformedResponse ? [] : ['success' => true, 'data' => ['id' => self::TICKET]], $this->backendStatus,
        )]);
    }

    public function test_both_forms_request_a_message_with_an_optional_order_reference(): void
    {
        foreach (['legal.refunds', 'subscription.cancel'] as $route) {
            $this->get(route($route))->assertOk()->assertSee('name="message" required', false)
                ->assertSee('numero d’ordine')->assertDontSee('name="order_reference"', false)
                ->assertDontSee('name="purchase_date"', false)->assertDontSee('vengono verificati tramite l’ordine');
        }
    }

    public function test_both_forms_open_signed_tickets_without_any_purchase_or_account(): void
    {
        foreach (['promo-withdrawal', 'subscription-non-renewal'] as $source) {
            $payload = $this->payload($source);
            $this->post($this->action($source), $payload)->assertRedirect($this->page($source))
                ->assertSessionHas('commerce_ticket.'.$source, self::TICKET);
            $this->get($this->page($source))->assertOk()->assertSee('La tua richiesta è stata ricevuta dall’assistenza.');
        }
        $requests = $this->tickets();
        $this->assertCount(2, $requests);
        foreach ($requests as [$request]) {
            $timestamp = $request->header('X-Wayout-Timestamp')[0];
            $this->assertSame(['category', 'title', 'message', 'priority', 'contact_email', 'contact_name', 'context'], array_keys($request->data()));
            $this->assertSame($request['context']['source'] === 'promo-withdrawal' ? 'RECESSO_E_RIMBORSO' : 'DISDETTA_ABBONAMENTO', $request['category']);
            $this->assertSame('NORMAL', $request['priority']);
            $this->assertSame('ada@example.com', $request['contact_email']);
            $this->assertSame('Ada Lovelace', $request['contact_name']);
            $this->assertStringContainsString('WO-ESTERNO-123', $request['message']);
            $this->assertStringContainsString('Telefono di contatto: +39 333 1234567', $request['message']);
            $this->assertSame('it-IT', $request['context']['locale']);
            $this->assertFalse($request->hasHeader('Authorization'));
            $this->assertSame(hash_hmac('sha256', $timestamp.'.'.$request->body(), str_repeat('s', 48)), $request->header('X-Wayout-Signature')[0]);
        }
        Http::assertNotSent(fn (Request $request) => str_contains($request->url(), '/subscription') || str_contains($request->url(), '/purchase-eligibility'));
        $this->assertDatabaseCount('purchases', 0);
        $this->assertDatabaseCount('withdrawal_requests', 0);
        $this->assertSame(2, DB::table('purchase_support_requests')->whereNull('purchase_id')->where('status', 'sent')->count());
    }

    public function test_message_without_order_number_is_accepted_and_client_cannot_override_ticket_context(): void
    {
        $payload = $this->payload('promo-withdrawal');
        $payload['message'] = 'Vorrei richiedere il rimborso, ma non trovo il mio ordine.';
        $payload['context'] = ['source' => 'account-deletion'];
        $payload['priority'] = 'URGENT';
        $this->post($this->action('promo-withdrawal'), $payload)->assertSessionHas('commerce_ticket.promo-withdrawal', self::TICKET);
        Http::assertSent(fn (Request $request) => str_ends_with($request->url(), '/internal/support/tickets')
            && $request['context']['source'] === 'promo-withdrawal' && $request['priority'] === 'NORMAL');
    }

    public function test_duplicate_submission_reuses_confirmed_ticket(): void
    {
        $payload = $this->payload('subscription-non-renewal');
        for ($i = 0; $i < 2; $i++) {
            $this->post($this->action('subscription-non-renewal'), $payload)->assertSessionHas('commerce_ticket.subscription-non-renewal', self::TICKET);
        }
        $this->assertCount(1, $this->tickets());
    }

    public function test_uncertain_delivery_is_not_successful_and_is_not_repeated(): void
    {
        $payload = $this->payload('promo-withdrawal');
        $this->backendStatus = 503;
        for ($i = 0; $i < 2; $i++) {
            $this->post($this->action('promo-withdrawal'), $payload)->assertSessionHasErrors('support')
                ->assertSessionMissing('commerce_ticket.promo-withdrawal');
        }
        $this->assertCount(1, $this->tickets());
        $this->assertDatabaseHas('purchase_support_requests', ['status' => 'uncertain']);
    }

    public function test_a_new_form_after_success_can_send_a_new_request(): void
    {
        $first = $this->payload('promo-withdrawal');
        $this->post($this->action('promo-withdrawal'), $first)->assertSessionHas('commerce_ticket.promo-withdrawal', self::TICKET);
        $this->get($this->page('promo-withdrawal'))->assertOk();
        $next = $this->payload('promo-withdrawal');
        $this->assertNotSame($first['request_id'], $next['request_id']);
        $this->post($this->action('promo-withdrawal'), $next)->assertSessionHas('commerce_ticket.promo-withdrawal', self::TICKET);
        $this->assertCount(2, $this->tickets());
    }

    public function test_invalid_success_response_is_not_a_confirmed_ticket(): void
    {
        $payload = $this->payload('subscription-non-renewal');
        $this->malformedResponse = true;
        $this->post($this->action('subscription-non-renewal'), $payload)->assertSessionHasErrors('support')
            ->assertSessionMissing('commerce_ticket.subscription-non-renewal');
    }

    public function test_message_confirmation_and_valid_session_are_required(): void
    {
        $payload = $this->payload('subscription-non-renewal');
        $this->post($this->action('subscription-non-renewal'), [...$payload, 'message' => ''])->assertSessionHasErrors('message');
        $this->post($this->action('subscription-non-renewal'), [...$payload, 'request_confirmed' => false])->assertSessionHasErrors('request_confirmed');
        $this->post($this->action('subscription-non-renewal'), [...$payload, 'request_id' => DatabaseUuid::new()])->assertSessionHasErrors('support');
        $this->assertCount(0, $this->tickets());
    }

    public function test_honeypot_captcha_and_ip_throttle_block_submission(): void
    {
        $payload = $this->payload('subscription-non-renewal');
        $this->post($this->action('subscription-non-renewal'), [...$payload, 'website' => 'spam'])->assertSessionHasErrors('website');
        config()->set('services.recaptcha.enabled', true);
        for ($i = 0; $i < 2; $i++) {
            $this->post($this->action('subscription-non-renewal'), $payload)->assertSessionHasErrors('recaptcha');
        }
        $this->post($this->action('subscription-non-renewal'), $payload)->assertStatus(429);
        $this->assertCount(0, $this->tickets());
    }

    private function page(string $source): string
    {
        return route($source === 'promo-withdrawal' ? 'legal.refunds' : 'subscription.cancel');
    }

    private function action(string $source): string
    {
        return route($source === 'promo-withdrawal' ? 'withdrawal.review.store' : 'subscription.cancel.store');
    }

    private function payload(string $source): array
    {
        $this->get($this->page($source))->assertOk();

        return ['request_id' => session('commerce_support.'.$source), 'name' => 'Ada Lovelace', 'email' => 'ada@example.com',
            'phone' => '+39 333 1234567', 'message' => 'Vorrei assistenza per il mio ordine WO-ESTERNO-123.', 'request_confirmed' => true];
    }

    public function test_phone_is_required_for_both_forms_and_invalid_values_never_create_a_ticket(): void
    {
        foreach (['promo-withdrawal', 'subscription-non-renewal'] as $source) {
            $this->get($this->page($source))->assertOk()->assertSee('type="tel" name="phone"', false)
                ->assertSee('required maxlength="40" autocomplete="tel"', false);
            $payload = $this->payload($source);
            unset($payload['phone']);
            $this->post($this->action($source), $payload)->assertSessionHasErrors('phone');
            $this->post($this->action($source), [...$payload, 'phone' => '12'])->assertSessionHasErrors('phone');
        }
        $this->assertCount(0, $this->tickets());
    }

    private function tickets(): \Illuminate\Support\Collection
    {
        return Http::recorded(fn (Request $request) => str_ends_with($request->url(), '/internal/support/tickets'));
    }
}
