<?php

namespace Tests\Feature;

use App\Mail\WaitlistVerificationMail;
use App\Mail\WaitlistWelcomeMail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class WaitlistTest extends TestCase
{
    use RefreshDatabase;

    public function test_home_shows_email_only_form_and_consents_below_it(): void
    {
        $this->get(route('home'))
            ->assertOk()
            ->assertSee('name="email"', false)
            ->assertSee('name="waitlist_terms_accepted"', false)
            ->assertSee('Completando l’iscrizione alla waitlist')
            ->assertSee('comunicazioni di marketing')
            ->assertDontSee('name="phone_number"', false)
            ->assertDontSee('firebase', false);
    }

    public function test_signup_creates_pending_entry_records_consents_and_sends_magic_link(): void
    {
        Mail::fake();

        $this->post(route('waitlist.store'), [
            'email' => 'Ada@Example.com',
            'waitlist_terms_accepted' => '1',
            'marketing_consent' => '1',
        ])->assertSessionHas('waitlist_verification_sent', true);

        $entry = DB::table('waitlist_entries')->where('email', 'ada@example.com')->first();
        $this->assertNotNull($entry);
        $this->assertNotNull($entry->benefit_id);
        $this->assertNull($entry->email_verified_at);
        $this->assertNull($entry->waitlist_position);
        $this->assertTrue((bool) $entry->marketing_consent);
        $this->assertDatabaseHas('consent_events', [
            'waitlist_entry_id' => $entry->id,
            'consent_type' => 'waitlist_legal',
            'source' => 'waitlist_email_form',
        ]);
        $this->assertDatabaseHas('consent_events', [
            'waitlist_entry_id' => $entry->id,
            'consent_type' => 'marketing',
            'action' => 'granted',
        ]);
        Mail::assertSent(WaitlistVerificationMail::class, fn ($mail) => $mail->hasTo('ada@example.com'));
    }

    public function test_signup_requires_terms_acceptance(): void
    {
        Mail::fake();

        $this->post(route('waitlist.store'), ['email' => 'no-consent@example.com'])
            ->assertSessionHasErrors('waitlist_terms_accepted');

        $this->assertDatabaseMissing('waitlist_entries', ['email' => 'no-consent@example.com']);
        Mail::assertNothingSent();
    }

    public function test_signup_confirmation_is_displayed_in_an_accessible_popup(): void
    {
        Mail::fake();

        $this->followingRedirects()->post(route('waitlist.store'), [
            'email' => 'popup@example.com',
            'waitlist_terms_accepted' => '1',
        ])->assertOk()
            ->assertSee('id="waitlist-verification-sent"', false)
            ->assertSee('role="dialog"', false)
            ->assertSee('aria-modal="true"', false)
            ->assertSee('Controlla la tua email')
            ->assertSee('Controlla la tua casella email');
    }

    public function test_magic_link_get_is_safe_and_post_verifies_email_assigns_position_and_sends_welcome(): void
    {
        Mail::fake();
        $this->post(route('waitlist.store'), ['email' => 'member@example.com', 'waitlist_terms_accepted' => '1']);
        $verificationMail = Mail::sent(WaitlistVerificationMail::class)->first();
        $token = basename(parse_url($verificationMail->verificationUrl, PHP_URL_PATH));

        $this->get($verificationMail->verificationUrl)->assertOk()->assertSee('Stiamo verificando');
        $this->assertNull(DB::table('waitlist_entries')->where('email', 'member@example.com')->value('email_verified_at'));

        $this->post(route('waitlist.verify', ['token' => $token]))
            ->assertRedirect(route('home'))
            ->assertSessionHas('waitlist_offer', true);

        $entry = DB::table('waitlist_entries')->where('email', 'member@example.com')->first();
        $this->assertNotNull($entry->email_verified_at);
        $this->assertSame(1, (int) $entry->waitlist_position);
        $this->assertSame($entry->id, session('waitlist_verified_entry_id'));
        Mail::assertSent(WaitlistWelcomeMail::class, fn ($mail) => $mail->hasTo('member@example.com'));
    }

    public function test_verified_member_reentering_email_sees_already_registered_offer_without_another_magic_link(): void
    {
        Mail::fake();
        $payload = [
            'email' => 'returning@example.com',
            'waitlist_terms_accepted' => '1',
        ];

        $this->post(route('waitlist.store'), $payload);
        $verificationMail = Mail::sent(WaitlistVerificationMail::class)->first();
        $token = basename(parse_url($verificationMail->verificationUrl, PHP_URL_PATH));
        $this->post(route('waitlist.verify', ['token' => $token]));

        $this->post(route('waitlist.store'), $payload)
            ->assertRedirect(route('home'))
            ->assertSessionHas('waitlist_offer', true)
            ->assertSessionHas('waitlist_status', 'already_registered')
            ->assertSessionMissing('waitlist_verification_sent');

        Mail::assertSent(WaitlistVerificationMail::class, 1);

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('Sei già nella waitlist')
            ->assertSee('returning@example.com');
    }

    public function test_expired_magic_link_does_not_verify_email(): void
    {
        Mail::fake();
        $this->post(route('waitlist.store'), ['email' => 'expired@example.com', 'waitlist_terms_accepted' => '1']);
        $mail = Mail::sent(WaitlistVerificationMail::class)->first();
        $token = basename(parse_url($mail->verificationUrl, PHP_URL_PATH));
        DB::table('waitlist_email_verifications')->update(['expires_at' => now()->subMinute()]);

        $this->post(route('waitlist.verify', ['token' => $token]))
            ->assertRedirect(route('home'))
            ->assertSessionHas('waitlist_error');
        $this->assertNull(DB::table('waitlist_entries')->where('email', 'expired@example.com')->value('email_verified_at'));
    }

    public function test_pending_entries_do_not_consume_capacity(): void
    {
        Mail::fake();
        DB::table('founder_settings')->where('key', 'waitlist_capacity')->update(['value' => 1]);
        $this->post(route('waitlist.store'), ['email' => 'first@example.com', 'waitlist_terms_accepted' => '1']);
        $this->post(route('waitlist.store'), ['email' => 'second@example.com', 'waitlist_terms_accepted' => '1']);

        $this->assertSame(2, DB::table('waitlist_entries')->count());
        $this->assertSame(0, DB::table('waitlist_entries')->whereNotNull('email_verified_at')->count());
    }

    public function test_duplicate_submission_is_idempotent_and_respects_resend_cooldown(): void
    {
        Mail::fake();
        $payload = ['email' => 'same@example.com', 'waitlist_terms_accepted' => '1'];
        $this->post(route('waitlist.store'), $payload);
        $this->post(route('waitlist.store'), $payload);

        $this->assertSame(1, DB::table('waitlist_entries')->where('email', 'same@example.com')->count());
        Mail::assertSentCount(1);
    }

    public function test_honeypot_blocks_signup(): void
    {
        Mail::fake();
        $this->post(route('waitlist.store'), [
            'email' => 'bot@example.com',
            'website' => 'spam',
        ])->assertSessionHas('waitlist_error');

        $this->assertDatabaseMissing('waitlist_entries', ['email' => 'bot@example.com']);
        Mail::assertNothingSent();
    }
}
