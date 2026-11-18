<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class ContactTicketTest extends TestCase
{
    use RefreshDatabase;

    private int $backendStatus = 201;

    protected function setUp(): void
    {
        parent::setUp();
        config()->set('email.contact_recipient', null);
        Http::fake(['*/internal/support/tickets' => fn () => Http::response([
            'success' => true, 'data' => ['id' => '36897688-e434-4dc0-9561-a134e9c4bdf2'],
        ], $this->backendStatus)]);
    }

    public static function subjects(): array
    {
        return [
            ['club', 'CLUB_E_VENUE'], ['collaboration', 'COLLABORAZIONI'],
            ['information', 'CURIOSITA_E_INFORMAZIONI'], ['other', 'ALTRO'],
        ];
    }

    #[DataProvider('subjects')]
    public function test_contact_subject_maps_to_the_correct_signed_backend_ticket_category(string $subject, string $category): void
    {
        $payload = $this->payload($subject);
        $this->from(route('contact'))->post(route('contact.store'), [...$payload, 'category' => 'ACCOUNT', 'priority' => 'URGENT'])
            ->assertRedirect(route('contact'))->assertSessionHas('contact_success', true);
        Http::assertSent(function (Request $request) use ($category, $subject) {
            if (! str_ends_with($request->url(), '/internal/support/tickets')) {
                return false;
            }
            $timestamp = $request->header('X-Wayout-Timestamp')[0];

            return $request['category'] === $category && $request['priority'] === 'NORMAL'
                && $request['title'] === __('messages.contact.subject_options.'.$subject)
                && $request['context']['source'] === 'contact' && $request['context']['page_url'] === route('contact')
                && $request['contact_email'] === 'mario@example.com' && $request['contact_name'] === 'Mario Rossi'
                && str_contains($request['message'], 'Messaggio per assistenza.')
                && ! $request->hasHeader('Authorization')
                && $request->header('X-Wayout-Signature')[0] === hash_hmac('sha256', $timestamp.'.'.$request->body(), str_repeat('s', 48));
        });
        $this->assertDatabaseHas('contact_messages', ['id' => $payload['request_id'], 'email' => 'mario@example.com']);
        $this->assertDatabaseHas('consent_events', ['contact_message_id' => $payload['request_id'], 'consent_type' => 'contact_legal']);
        $this->assertDatabaseHas('purchase_support_requests', ['id' => $payload['request_id'], 'source' => 'contact', 'status' => 'sent']);
    }

    public function test_duplicate_contact_submission_does_not_duplicate_messages_consents_or_tickets(): void
    {
        $payload = $this->payload();
        for ($i = 0; $i < 2; $i++) {
            $this->post(route('contact.store'), $payload)->assertSessionHas('contact_success', true);
        }
        $this->assertDatabaseCount('contact_messages', 1);
        $this->assertDatabaseCount('consent_events', 1);
        $this->assertCount(1, Http::recorded(fn (Request $request) => str_ends_with($request->url(), '/internal/support/tickets')));
    }

    public function test_backend_failure_preserves_local_message_and_consents_without_showing_success(): void
    {
        $payload = $this->payload();
        $this->backendStatus = 503;
        $this->post(route('contact.store'), $payload)->assertSessionHas('contact_error')->assertSessionMissing('contact_success');
        $this->post(route('contact.store'), $payload)->assertSessionHas('contact_error');
        $this->assertDatabaseCount('contact_messages', 1);
        $this->assertDatabaseCount('consent_events', 1);
        $this->assertDatabaseHas('purchase_support_requests', ['status' => 'uncertain']);
        $this->assertCount(1, Http::recorded(fn (Request $request) => str_ends_with($request->url(), '/internal/support/tickets')));
    }

    public function test_unsupported_subject_and_unaccepted_privacy_do_not_send_a_ticket(): void
    {
        $payload = $this->payload();
        $this->post(route('contact.store'), [...$payload, 'subject' => 'purchase'])->assertSessionHasErrors('subject');
        $this->post(route('contact.store'), [...$payload, 'privacy_accepted' => false])->assertSessionHasErrors('privacy_accepted');
        $this->assertDatabaseCount('contact_messages', 0);
        $this->assertCount(0, Http::recorded(fn (Request $request) => str_ends_with($request->url(), '/internal/support/tickets')));
    }

    private function payload(string $subject = 'information'): array
    {
        $this->get(route('contact'))->assertOk();

        return [
            'request_id' => session('contact_request_id'), 'name' => 'Mario Rossi', 'email' => 'mario@example.com',
            'subject' => $subject, 'message' => 'Messaggio per assistenza.', 'privacy_accepted' => true,
        ];
    }
}
