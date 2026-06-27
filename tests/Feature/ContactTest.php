<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class ContactTest extends TestCase
{
    use RefreshDatabase;

    public function test_contact_message_is_saved(): void
    {
        $response = $this->from(route('contact'))
            ->post(route('contact.store'), [
                'name' => 'Mario Rossi',
                'email' => 'mario@example.com',
                'subject' => 'Info',
                'message' => 'Vorrei maggiori informazioni.',
            ]);

        $response->assertRedirect(route('contact'))
            ->assertSessionHas('contact_success', true);

        $this->assertDatabaseHas('contact_messages', [
            'email' => 'mario@example.com',
            'message' => 'Vorrei maggiori informazioni.',
        ]);
    }

    public function test_contact_database_failure_returns_a_user_friendly_error(): void
    {
        Schema::dropIfExists('contact_messages');

        $response = $this->from(route('contact'))
            ->post(route('contact.store'), [
                'name' => 'Mario Rossi',
                'email' => 'mario@example.com',
                'subject' => 'Info',
                'message' => 'Vorrei maggiori informazioni.',
            ]);

        $response->assertRedirect(route('contact'))
            ->assertSessionHas('contact_error', 'Non siamo riusciti a inviare il messaggio. Riprova tra qualche minuto.');
    }

    public function test_contact_honeypot_blocks_spam_submission(): void
    {
        $response = $this->from(route('contact'))
            ->post(route('contact.store'), [
                'name' => 'Mario Rossi',
                'email' => 'mario@example.com',
                'subject' => 'Info',
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
