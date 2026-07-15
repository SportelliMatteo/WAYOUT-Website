<?php

namespace Tests\Feature;

use App\Mail\WaitlistWelcomeMail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class WaitlistTest extends TestCase
{
    use RefreshDatabase;

    public function test_new_email_is_added_to_the_waitlist_and_prompts_for_profile(): void
    {
        $response = $this->from(route('home'))
            ->post(route('waitlist.store'), [
                'email' => 'NewPerson@Example.com',
            ]);

        $response->assertRedirect(route('home'))
            ->assertSessionHas('waitlist_profile_prompt', true)
            ->assertSessionHas('waitlist_status', 'registered')
            ->assertSessionHas('waitlist_email', 'newperson@example.com');

        $this->assertDatabaseHas('waitlist_entries', [
            'email' => 'newperson@example.com',
            'offer_shown' => true,
        ]);

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('Completando l’iscrizione alla waitlist')
            ->assertDontSee('name="legal_terms_accepted"', false)
            ->assertDontSee('Versioni documenti:');
    }

    public function test_existing_incomplete_email_is_not_inserted_again_but_still_prompts_for_profile(): void
    {
        DB::table('waitlist_entries')->insert([
            'email' => 'already@example.com',
            'offer_shown' => true,
            'created_at' => now()->subDay(),
            'updated_at' => now()->subDay(),
        ]);

        $response = $this->from(route('home'))
            ->post(route('waitlist.store'), [
                'email' => 'already@example.com',
            ]);

        $response->assertRedirect(route('home'))
            ->assertSessionHas('waitlist_profile_prompt', true)
            ->assertSessionHas('waitlist_status', 'registered')
            ->assertSessionHas('waitlist_email', 'already@example.com');

        $this->assertSame(1, DB::table('waitlist_entries')
            ->where('email', 'already@example.com')
            ->count());
    }

    public function test_welcome_email_is_sent_only_after_the_profile_is_completed_and_only_once(): void
    {
        Mail::fake();

        $this->post(route('waitlist.store'), ['email' => 'ada@example.com']);

        Mail::assertNothingSent();

        $profile = [
            'email' => 'ada@example.com',
            'first_name' => 'Ada',
            'last_name' => 'Lovelace',
            'birth_date' => '1990-01-01',
            'phone_prefix' => '+39',
            'phone_number' => '3331234567',
        ];

        $this->post(route('waitlist.profile'), $profile);
        $this->post(route('waitlist.profile'), $profile);

        Mail::assertSent(WaitlistWelcomeMail::class, 1);
        Mail::assertSent(WaitlistWelcomeMail::class, fn (WaitlistWelcomeMail $mail) =>
            $mail->hasTo('ada@example.com') && $mail->member['first_name'] === 'Ada'
        );

        $this->assertNotNull(DB::table('waitlist_entries')
            ->where('email', 'ada@example.com')
            ->value('welcome_email_sent_at'));
    }

    public function test_disabled_email_switch_prevents_waitlist_delivery(): void
    {
        Mail::fake();
        config()->set('email.enabled', false);

        $this->post(route('waitlist.store'), ['email' => 'ada@example.com']);
        $this->post(route('waitlist.profile'), [
            'email' => 'ada@example.com',
            'first_name' => 'Ada',
            'last_name' => 'Lovelace',
            'birth_date' => '1990-01-01',
            'phone_prefix' => '+39',
            'phone_number' => '3331234567',
        ]);

        Mail::assertNothingSent();
        $this->assertNull(DB::table('waitlist_entries')
            ->where('email', 'ada@example.com')
            ->value('welcome_email_sent_at'));
    }

    public function test_existing_complete_profile_collects_legal_acceptance_before_retrying_welcome_email(): void
    {
        Mail::fake();

        DB::table('waitlist_entries')->insert([
            'email' => 'ada@example.com',
            'first_name' => 'Ada',
            'last_name' => 'Lovelace',
            'birth_date' => '1990-01-01',
            'phone_prefix' => '+39',
            'phone_number' => '3331234567',
            'offer_shown' => true,
            'welcome_email_sent_at' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->post(route('waitlist.store'), ['email' => 'ada@example.com'])
            ->assertSessionHas('waitlist_profile_prompt', true);

        Mail::assertNothingSent();

        $this->post(route('waitlist.profile'), [
            'email' => 'ada@example.com',
            'first_name' => 'Ada',
            'last_name' => 'Lovelace',
            'birth_date' => '1990-01-01',
            'phone_prefix' => '+39',
            'phone_number' => '3331234567',
        ]);

        Mail::assertSent(WaitlistWelcomeMail::class, 1);
        $this->assertNotNull(DB::table('waitlist_entries')
            ->where('email', 'ada@example.com')
            ->value('welcome_email_sent_at'));

        $this->post(route('waitlist.store'), ['email' => 'ada@example.com']);
        Mail::assertSent(WaitlistWelcomeMail::class, 1);
    }

    public function test_existing_email_message_is_rendered_with_the_founder_offer(): void
    {
        DB::table('waitlist_entries')->insert([
            'email' => 'already@example.com',
            'offer_shown' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->withSession([
            'waitlist_offer' => true,
            'waitlist_status' => 'already_registered',
            'waitlist_email' => 'already@example.com',
        ])->get(route('home'));

        $response->assertOk()
            ->assertSee('Sei già nella waitlist')
            ->assertSee('Scopri i Founder Pass');
    }

    public function test_existing_waitlist_email_with_purchase_shows_purchased_plan(): void
    {
        DB::table('waitlist_entries')->insert([
            'email' => 'buyer@example.com',
            'offer_shown' => true,
            'created_at' => now()->subDay(),
            'updated_at' => now()->subDay(),
        ]);

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
            ->post(route('waitlist.store'), [
                'email' => 'buyer@example.com',
            ]);

        $response->assertRedirect(route('home'))
            ->assertSessionHas('waitlist_status', 'already_registered')
            ->assertSessionHas('purchased_plan.name', 'Founder 12M Creator Pass')
            ->assertSessionHas('purchased_plan.amount', 5900);
    }

    public function test_purchased_plan_message_is_rendered_without_purchase_cta(): void
    {
        $response = $this->withSession([
            'waitlist_offer' => true,
            'waitlist_status' => 'already_registered',
            'waitlist_email' => 'buyer@example.com',
            'purchased_plan' => [
                'code' => 'creator',
                'name' => 'Founder 12M Creator Pass',
                'amount' => 5900,
                'currency' => 'eur',
            ],
        ])->get(route('home'));

        $response->assertOk()
            ->assertSee('Hai già acquistato un Founder Pass')
            ->assertSee('Founder 12M Creator Pass')
            ->assertSee('59,00€')
            ->assertSee('Reinvia email di conferma')
            ->assertDontSee('Scopri i Founder Pass');
    }

    public function test_waitlist_database_failure_returns_a_user_friendly_error(): void
    {
        Schema::dropIfExists('waitlist_entries');

        $response = $this->from(route('home'))
            ->post(route('waitlist.store'), [
                'email' => 'person@example.com',
            ]);

        $response->assertRedirect(route('home'))
            ->assertSessionHas('waitlist_error', 'Non siamo riusciti a completare l’iscrizione. Riprova tra qualche minuto.');
    }

    public function test_waitlist_honeypot_blocks_spam_submission(): void
    {
        $response = $this->from(route('home'))
            ->post(route('waitlist.store'), [
                'email' => 'person@example.com',
                'website' => 'https://spam.example',
            ]);

        $response->assertRedirect(route('home'))
            ->assertSessionHas('waitlist_error', 'Non siamo riusciti a completare l’iscrizione. Riprova tra qualche minuto.');

        $this->assertDatabaseMissing('waitlist_entries', [
            'email' => 'person@example.com',
        ]);
    }
}
