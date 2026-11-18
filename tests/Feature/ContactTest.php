<?php

namespace Tests\Feature;

use App\Mail\ContactMessageMail;
use App\Support\DatabaseUuid;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class ContactTest extends TestCase
{
    use RefreshDatabase;

    private string $requestId;

    protected function setUp(): void
    {
        parent::setUp();
        $this->requestId = DatabaseUuid::new();
        $this->withSession(['contact_request_id' => $this->requestId]);
        Http::fake(['*/internal/support/tickets' => Http::response([
            'success' => true, 'data' => ['id' => '36897688-e434-4dc0-9561-a134e9c4bdf2'],
        ], 201)]);
    }

    public function test_contact_message_is_saved(): void
    {
        Mail::fake();
        config()->set('email.contact_recipient', 'support@wayout.test');

        $response = $this->from(route('contact'))
            ->post(route('contact.store'), [
                'request_id' => $this->requestId,
                'name' => 'Mario Rossi',
                'email' => 'mario@example.com',
                'subject' => 'information',
                'message' => 'Vorrei maggiori informazioni.',
                'privacy_accepted' => '1',
            ]);

        $response->assertRedirect(route('contact'))
            ->assertSessionHas('contact_success', true);

        $this->assertDatabaseHas('contact_messages', [
            'email' => 'mario@example.com',
            'subject' => 'Curiosità e informazioni',
            'message' => 'Vorrei maggiori informazioni.',
        ]);

        Mail::assertSent(ContactMessageMail::class, function (ContactMessageMail $mail) {
            return $mail->hasTo('support@wayout.test')
                && $mail->contact['email'] === 'mario@example.com'
                && $mail->contact['subject'] === 'Curiosità e informazioni';
        });
    }

    public function test_contact_message_is_saved_without_email_when_sending_is_disabled(): void
    {
        Mail::fake();
        config()->set('email.enabled', false);
        config()->set('email.contact_recipient', 'support@wayout.test');

        $this->post(route('contact.store'), [
            'request_id' => $this->requestId,
                'name' => 'Mario Rossi',
            'email' => 'mario@example.com',
            'subject' => 'information',
            'message' => 'Vorrei maggiori informazioni.',
            'privacy_accepted' => '1',
        ])->assertSessionHas('contact_success', true);

        $this->assertDatabaseHas('contact_messages', ['email' => 'mario@example.com']);
        Mail::assertNothingSent();
    }

    public function test_contact_database_failure_returns_a_user_friendly_error(): void
    {
        if (DB::getDriverName() === 'mysql') {
            $this->markTestSkipped('La simulazione elimina una tabella e MySQL esegue un commit DDL implicito; il caso è coperto dalla suite SQLite.');
        }

        Schema::withoutForeignKeyConstraints(fn () => Schema::dropIfExists('contact_messages'));

        $response = $this->from(route('contact'))
            ->post(route('contact.store'), [
                'request_id' => $this->requestId,
                'name' => 'Mario Rossi',
                'email' => 'mario@example.com',
                'subject' => 'information',
                'message' => 'Vorrei maggiori informazioni.',
                'privacy_accepted' => '1',
            ]);

        $response->assertRedirect(route('contact'))
            ->assertSessionHas('contact_error', 'Non siamo riusciti a inviare il messaggio. Riprova tra qualche minuto.');
    }

    public function test_contact_honeypot_blocks_spam_submission(): void
    {
        $response = $this->from(route('contact'))
            ->post(route('contact.store'), [
                'request_id' => $this->requestId,
                'name' => 'Mario Rossi',
                'email' => 'mario@example.com',
                'subject' => 'other',
                'message' => 'Vorrei maggiori informazioni.',
                'website' => 'https://spam.example',
            ]);

        $response->assertRedirect(route('contact'))
            ->assertSessionHas('contact_error', 'Non siamo riusciti a inviare il messaggio. Riprova tra qualche minuto.');

        $this->assertDatabaseMissing('contact_messages', [
            'email' => 'mario@example.com',
        ]);
    }
}
