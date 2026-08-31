<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class RecaptchaProtectionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config()->set('services.recaptcha.enabled', true);
        config()->set('services.recaptcha.site_key', 'test-site-key');
        config()->set('services.recaptcha.secret_key', 'test-secret-key');
        config()->set('services.recaptcha.minimum_score', 0.5);
        config()->set('services.recaptcha.verify_url', 'https://www.google.com/recaptcha/api/siteverify');
    }

    public function test_protected_forms_render_the_expected_recaptcha_actions(): void
    {
        $this->get(route('contact'))
            ->assertOk()
            ->assertSee('data-recaptcha-action="contact"', false)
            ->assertSee('data-recaptcha-site-key="test-site-key"', false);

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('data-recaptcha-action="waitlist"', false);

        $this->get(route('legal.refunds'))
            ->assertOk()
            ->assertSee('data-recaptcha-action="withdrawal_review"', false);

        $this->get(route('admin.login'))
            ->assertOk()
            ->assertSee('data-recaptcha-action="admin_login"', false)
            ->assertSee('data-recaptcha-action="admin_recover"', false);
    }

    public function test_valid_token_and_action_allow_a_contact_submission(): void
    {
        Mail::fake();
        Http::fake([
            'https://www.google.com/recaptcha/api/siteverify' => Http::response([
                'success' => true,
                'score' => 0.9,
                'action' => 'contact',
                'hostname' => 'wayout.test',
            ]),
        ]);

        $this->post(route('contact.store'), [
            'g-recaptcha-response' => 'valid-token',
            'name' => 'Mario Rossi',
            'email' => 'mario@example.com',
            'subject' => 'information',
            'message' => 'Vorrei maggiori informazioni.',
            'privacy_accepted' => '1',
        ])->assertSessionHas('contact_success', true);

        $this->assertDatabaseHas('contact_messages', ['email' => 'mario@example.com']);
        Http::assertSent(fn ($request) => $request['secret'] === 'test-secret-key'
            && $request['response'] === 'valid-token');
    }

    public function test_missing_token_blocks_a_protected_submission(): void
    {
        $this->post(route('contact.store'), [
            'name' => 'Bot',
            'email' => 'bot@example.com',
            'subject' => 'information',
            'message' => 'Spam',
            'privacy_accepted' => '1',
        ])->assertSessionHasErrors('recaptcha');

        $this->assertDatabaseMissing('contact_messages', ['email' => 'bot@example.com']);
        Http::assertNothingSent();
    }

    public function test_wrong_action_or_low_score_blocks_a_submission(): void
    {
        Http::fake([
            'https://www.google.com/recaptcha/api/siteverify' => Http::response([
                'success' => true,
                'score' => 0.4,
                'action' => 'waitlist',
            ]),
        ]);

        $this->post(route('contact.store'), [
            'g-recaptcha-response' => 'suspicious-token',
            'name' => 'Bot',
            'email' => 'bot@example.com',
            'subject' => 'information',
            'message' => 'Spam',
            'privacy_accepted' => '1',
        ])->assertSessionHasErrors('recaptcha');

        $this->assertDatabaseMissing('contact_messages', ['email' => 'bot@example.com']);
    }
}
