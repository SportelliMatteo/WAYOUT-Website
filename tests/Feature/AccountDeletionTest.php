<?php

namespace Tests\Feature;

use App\Support\AccountDeletionService;
use App\Support\DatabaseUuid;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class AccountDeletionTest extends TestCase
{
    use RefreshDatabase;

    private const TICKET = '751c0461-aaf7-4e08-879c-3523aa5d5676';

    private int $backendStatus = 201;

    protected function setUp(): void
    {
        parent::setUp();
        Http::fake(['*/internal/support/tickets' => fn () => Http::response([
            'success' => true, 'data' => ['id' => self::TICKET],
        ], $this->backendStatus)]);
    }

    public function test_form_displays_loss_warning_and_explicit_confirmation_without_an_order(): void
    {
        $this->get(route('account.deletion'))->assertOk()
            ->assertSee(AccountDeletionService::WARNING)
            ->assertSee('role="alert"', false)
            ->assertSee('type="tel" name="phone" value="" required', false)
            ->assertSee('name="consequences_accepted" value="1" required', false)
            ->assertDontSee('name="order_reference"', false)
            ->assertHeader('Cache-Control', 'no-store, private');
        $this->assertNotEmpty(session('account_deletion_request_id'));
    }

    public function test_request_is_signed_without_jwt_and_records_acknowledgement_without_deleting_an_account(): void
    {
        $payload = $this->payload();
        $this->post(route('account.deletion.store'), $payload)->assertRedirect(route('account.deletion'))
            ->assertSessionHas('account_deletion_ticket', self::TICKET);
        $this->get(route('account.deletion'))->assertOk()->assertSee('La richiesta di cancellazione dell’account è stata ricevuta.')
            ->assertSee('Il tuo account non è stato cancellato con questo invio.');
        Http::assertSent(function (Request $request) {
            if (! str_ends_with($request->url(), '/internal/support/tickets')) {
                return false;
            }
            $timestamp = $request->header('X-Wayout-Timestamp')[0];

            return $request['category'] === 'CANCELLAZIONE_ACCOUNT' && $request['context']['source'] === 'account-deletion'
                && $request['contact_email'] === 'ada@example.com'
                && str_contains($request['message'], AccountDeletionService::WARNING)
                && str_contains($request['message'], 'Telefono di contatto: +39 333 1234567')
                && ! $request->hasHeader('Authorization')
                && $request->header('X-Wayout-Signature')[0] === hash_hmac('sha256', $timestamp.'.'.$request->body(), str_repeat('s', 48));
        });
        $this->assertDatabaseHas('account_deletion_requests', [
            'id' => $payload['request_id'], 'status' => 'sent', 'backend_ticket_id' => self::TICKET,
            'warning_hash' => hash('sha256', AccountDeletionService::WARNING),
        ]);
        $this->assertNotNull(DB::table('account_deletion_requests')->value('acknowledged_at'));
        $this->assertNotSame('ada@example.com', DB::table('account_deletion_requests')->value('email_hash'));
    }

    public function test_confirmation_is_required_and_cannot_be_bypassed(): void
    {
        $payload = $this->payload();
        $payload['consequences_accepted'] = false;
        $this->post(route('account.deletion.store'), $payload)->assertSessionHasErrors('consequences_accepted');
        $this->assertDatabaseCount('account_deletion_requests', 0);
        $this->assertCount(0, $this->tickets());
    }

    public function test_repeated_form_submission_does_not_create_duplicate_tickets(): void
    {
        $payload = $this->payload();
        $this->post(route('account.deletion.store'), $payload)->assertSessionHas('account_deletion_ticket', self::TICKET);
        $this->post(route('account.deletion.store'), $payload)->assertSessionHas('account_deletion_ticket', self::TICKET);
        $this->assertCount(1, $this->tickets());
    }

    public function test_a_new_form_after_a_successful_submission_opens_a_new_ticket(): void
    {
        $first = $this->payload();
        $this->post(route('account.deletion.store'), $first)->assertSessionHas('account_deletion_ticket', self::TICKET);
        $this->get(route('account.deletion'))->assertOk();
        $next = $this->payload();
        $this->assertNotSame($first['request_id'], $next['request_id']);
        $this->post(route('account.deletion.store'), $next)->assertSessionHas('account_deletion_ticket', self::TICKET);
        $this->assertCount(2, $this->tickets());
        $this->assertDatabaseCount('account_deletion_requests', 2);
    }

    public function test_old_completed_form_is_rotated_even_after_the_success_message_has_expired(): void
    {
        $first = $this->payload();
        $this->post(route('account.deletion.store'), $first)->assertSessionHas('account_deletion_ticket', self::TICKET);
        $this->get('/privacy-policy')->assertOk();
        $this->get('/privacy-policy')->assertOk();
        $next = $this->payload();
        $this->assertNotSame($first['request_id'], $next['request_id']);
        $this->post(route('account.deletion.store'), $next)->assertSessionHas('account_deletion_ticket', self::TICKET);
        $this->assertCount(2, $this->tickets());
    }

    public function test_forged_request_id_and_honeypot_are_rejected(): void
    {
        $payload = $this->payload();
        $this->post(route('account.deletion.store'), [...$payload, 'request_id' => DatabaseUuid::new()])->assertSessionHasErrors('support');
        $this->post(route('account.deletion.store'), [...$payload, 'website' => 'spam'])->assertSessionHasErrors('website');
        $this->assertCount(0, $this->tickets());
    }

    public function test_uncertain_delivery_does_not_claim_success_or_resend(): void
    {
        $payload = $this->payload();
        $this->backendStatus = 503;
        $this->post(route('account.deletion.store'), $payload)->assertSessionHasErrors('support')->assertSessionMissing('account_deletion_ticket');
        $this->post(route('account.deletion.store'), $payload)->assertSessionHasErrors('support');
        $this->assertCount(1, $this->tickets());
        $this->assertDatabaseHas('account_deletion_requests', ['status' => 'uncertain']);
    }

    public function test_captcha_and_ip_throttle_protect_the_form(): void
    {
        config()->set('services.recaptcha.enabled', true);
        $payload = $this->payload();
        for ($i = 0; $i < 3; $i++) {
            $this->post(route('account.deletion.store'), $payload)->assertSessionHasErrors('recaptcha');
        }
        $this->post(route('account.deletion.store'), $payload)->assertStatus(429);
        $this->assertCount(0, $this->tickets());
    }

    private function payload(): array
    {
        $this->get(route('account.deletion'))->assertOk();

        return ['request_id' => session('account_deletion_request_id'), 'name' => 'Ada Lovelace', 'email' => 'ada@example.com', 'phone' => '+39 333 1234567', 'consequences_accepted' => '1'];
    }

    public function test_phone_is_required_and_must_have_a_valid_format(): void
    {
        $payload = $this->payload();
        unset($payload['phone']);
        $this->post(route('account.deletion.store'), $payload)->assertSessionHasErrors('phone');
        $this->post(route('account.deletion.store'), [...$payload, 'phone' => 'abc123'])->assertSessionHasErrors('phone');
        $this->assertCount(0, $this->tickets());
    }

    private function tickets(): Collection
    {
        return Http::recorded(fn (Request $request) => str_ends_with($request->url(), '/internal/support/tickets'));
    }
}
