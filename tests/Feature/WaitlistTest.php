<?php

namespace Tests\Feature;

use App\Mail\PurchaseConfirmationMail;
use App\Mail\WaitlistWelcomeMail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class WaitlistTest extends TestCase
{
    use RefreshDatabase;

    public function test_purchase_confirmation_resend_feedback_is_visible_in_the_offer_modal(): void
    {
        $purchasedPlan = [
            'code' => 'join',
            'name' => 'Founder 12M Join Pass',
            'amount' => 2900,
        ];

        $this->withSession([
            'waitlist_offer' => true,
            'waitlist_email' => 'buyer@example.com',
            'purchased_plan' => $purchasedPlan,
            'purchase_confirmation_success' => 'Email di conferma inviata.',
        ])->get(route('home'))
            ->assertOk()
            ->assertSee('Email di conferma inviata.')
            ->assertSee('purchase-confirmation-resend-submit', false);

        $this->withSession([
            'waitlist_offer' => true,
            'waitlist_email' => 'buyer@example.com',
            'purchased_plan' => $purchasedPlan,
            'purchase_confirmation_error' => 'Attendi prima di riprovare.',
        ])->get(route('home'))
            ->assertOk()
            ->assertSee('Attendi prima di riprovare.');
    }

    public function test_verified_phone_prompts_for_profile_without_creating_a_partial_entry(): void
    {
        $response = $this->from(route('home'))->beginPhoneRegistration();

        $response->assertRedirect(route('home'))
            ->assertSessionHas('waitlist_profile_prompt', true)
            ->assertSessionHas('waitlist_status', 'registered');

        $this->assertDatabaseCount('waitlist_entries', 0);

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('Completando l’iscrizione alla waitlist')
            ->assertSee('Termini sito e waitlist')
            ->assertDontSee('name="legal_terms_accepted"', false)
            ->assertDontSee('Versioni documenti:');
    }

    public function test_existing_incomplete_phone_is_not_inserted_again_but_still_prompts_for_profile(): void
    {
        DB::table('waitlist_entries')->insert([
            'email' => 'already@example.com',
            'phone_prefix' => '+39',
            'phone_number' => '3331234567',
            'offer_shown' => true,
            'created_at' => now()->subDay(),
            'updated_at' => now()->subDay(),
        ]);

        $response = $this->from(route('home'))->beginPhoneRegistration();

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
        config()->set('services.firebase.phone_verification_enabled', true);
        $this->fakeWayoutRegistrationApi();

        $this->beginPhoneRegistration();

        Mail::assertNothingSent();

        $profile = [
            'email' => 'ada@example.com',
            'first_name' => 'Ada',
            'last_name' => 'Lovelace',
            'birth_date' => '1990-01-01',
            'gender' => 'OTHER',
        ];

        $this->post(route('waitlist.profile'), $profile);
        $this->post(route('waitlist.profile'), $profile);

        Mail::assertSent(WaitlistWelcomeMail::class, 1);
        Mail::assertSent(WaitlistWelcomeMail::class, fn (WaitlistWelcomeMail $mail) => $mail->hasTo('ada@example.com') && $mail->member['first_name'] === 'Ada'
        );

        $this->assertNotNull(DB::table('waitlist_entries')
            ->where('email', 'ada@example.com')
            ->value('welcome_email_sent_at'));
        $this->assertDatabaseHas('waitlist_entries', [
            'email' => 'ada@example.com',
            'firebase_uid' => null,
            'phone_number' => '3331234567',
            'gender' => 'OTHER',
        ]);
        $this->assertNotNull(DB::table('waitlist_entries')
            ->where('email', 'ada@example.com')
            ->value('phone_verified_at'));

        Http::assertSentCount(2);
        Http::assertSent(fn ($request) => $request->url() === 'https://staging-app.wayoutapp.it/api/auth/verify-firebase-token'
            && $request['firebase_token'] === $this->firebaseTokenForPhone('+393331234567'));
        Http::assertSent(fn ($request) => $request->url() === 'https://staging-app.wayoutapp.it/api/auth/create-profile'
            && $request->hasHeader('X-Temp-Token', 'temporary-profile-token')
            && $request['mobile_number'] === '+393331234567'
            && $request['email'] === 'ada@example.com'
            && $request['gender'] === 'OTHER'
            && $request['profile_image'] === ''
            && is_string($request['nickname'])
            && $request['nickname'] !== '');
    }

    public function test_remote_profile_failure_does_not_create_a_local_waitlist_entry(): void
    {
        Mail::fake();
        config()->set('services.firebase.phone_verification_enabled', true);
        Http::fake([
            '*/api/auth/verify-firebase-token' => Http::response([
                'data' => ['temp_token' => 'temporary-profile-token'],
            ]),
            '*/api/auth/create-profile' => Http::response([
                'code' => 'PROFILE_CREATION_FAILED',
                'message' => 'Remote failure.',
            ], 500),
        ]);

        $this->beginPhoneRegistration();

        $this->post(route('waitlist.profile'), [
            'email' => 'ada@example.com',
            'first_name' => 'Ada',
            'last_name' => 'Lovelace',
            'birth_date' => '1990-01-01',
            'gender' => 'FEMALE',
        ])->assertSessionHas('waitlist_profile_prompt', true)
            ->assertSessionHas('waitlist_error', 'Non siamo riusciti a creare il profilo WAYOUT. Riprova tra qualche minuto.');

        $this->assertDatabaseCount('waitlist_entries', 0);
        Mail::assertNothingSent();
    }

    public function test_verified_firebase_token_cannot_be_used_for_a_different_phone(): void
    {
        config()->set('services.firebase.phone_verification_enabled', true);
        $this->fakeWayoutRegistrationApi();

        $this->beginPhoneRegistration(
            phoneNumber: '3339999999',
            firebaseToken: $this->firebaseTokenForPhone('+393331234567'),
        );

        $this->post(route('waitlist.profile'), [
            'email' => 'attacker@example.com',
            'first_name' => 'Test',
            'last_name' => 'Mismatch',
            'birth_date' => '1990-01-01',
            'gender' => 'MALE',
        ])->assertSessionHas('waitlist_profile_prompt', true)
            ->assertSessionHas('waitlist_error', 'Non siamo riusciti a creare il profilo WAYOUT. Riprova tra qualche minuto.');

        Http::assertSentCount(1);
        Http::assertNotSent(fn ($request) => str_ends_with($request->url(), '/api/auth/create-profile'));
        $this->assertDatabaseCount('waitlist_entries', 0);
    }

    public function test_phone_step_cannot_be_completed_without_a_verified_phone_token(): void
    {
        Mail::fake();
        config()->set('services.firebase.phone_verification_enabled', true);

        $this->post(route('waitlist.store'), [
            'phone_prefix' => '+39',
            'phone_number' => '3331234567',
        ])->assertSessionHasErrors('firebase_id_token');

        $this->assertDatabaseCount('waitlist_entries', 0);
        Mail::assertNothingSent();
    }

    public function test_firebase_token_is_not_stored_with_the_cookie_session_driver(): void
    {
        config()->set('services.firebase.phone_verification_enabled', true);
        config()->set('session.driver', 'cookie');

        $this->beginPhoneRegistration()
            ->assertSessionHas('waitlist_error', 'Non siamo riusciti a completare l’iscrizione. Riprova tra qualche minuto.');

        $this->assertNull(session('waitlist_phone_registration'));
        $this->assertDatabaseCount('waitlist_entries', 0);
    }

    public function test_phone_otp_is_skipped_when_firebase_verification_is_disabled(): void
    {
        Mail::fake();
        config()->set('services.firebase.phone_verification_enabled', false);

        $this->beginPhoneRegistration();

        $this->post(route('waitlist.profile'), [
            'email' => 'local-test@example.com',
            'first_name' => 'Local',
            'last_name' => 'Test',
            'birth_date' => '1990-01-01',
            'gender' => 'MALE',
        ])->assertSessionHas('waitlist_offer', true)
            ->assertSessionDoesntHaveErrors();

        $this->assertDatabaseHas('waitlist_entries', [
            'email' => 'local-test@example.com',
            'first_name' => 'Local',
            'phone_number' => '3331234567',
            'firebase_uid' => null,
            'phone_verified_at' => null,
        ]);
    }

    public function test_disabled_email_switch_prevents_waitlist_delivery(): void
    {
        Mail::fake();
        config()->set('email.enabled', false);

        $this->beginPhoneRegistration();
        $this->post(route('waitlist.profile'), [
            'email' => 'ada@example.com',
            'first_name' => 'Ada',
            'last_name' => 'Lovelace',
            'birth_date' => '1990-01-01',
            'gender' => 'FEMALE',
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
            'nickname' => 'ada_lovelace',
            'birth_date' => '1990-01-01',
            'gender' => 'FEMALE',
            'phone_prefix' => '+39',
            'phone_number' => '3331234567',
            'offer_shown' => true,
            'welcome_email_sent_at' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->beginPhoneRegistration()
            ->assertSessionHas('waitlist_profile_prompt', true);

        Mail::assertNothingSent();

        $this->post(route('waitlist.profile'), [
            'email' => 'ada@example.com',
            'first_name' => 'Ada',
            'last_name' => 'Lovelace',
            'birth_date' => '1990-01-01',
            'gender' => 'FEMALE',
        ]);

        Mail::assertSent(WaitlistWelcomeMail::class, 1);
        $this->assertNotNull(DB::table('waitlist_entries')
            ->where('email', 'ada@example.com')
            ->value('welcome_email_sent_at'));

        $this->beginPhoneRegistration();
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
        Mail::fake();

        DB::table('waitlist_entries')->insert([
            'email' => 'buyer@example.com',
            'nickname' => 'buyer_user',
            'gender' => 'MALE',
            'first_name' => 'Buyer',
            'last_name' => 'User',
            'birth_date' => '1990-01-01',
            'phone_prefix' => '+39',
            'phone_number' => '3331234567',
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

        $response = $this->from(route('home'))->beginPhoneRegistration();

        $response->assertRedirect(route('home'))
            ->assertSessionHas('waitlist_status', 'already_registered')
            ->assertSessionHas('purchased_plan.name', 'Founder 12M Creator Pass')
            ->assertSessionHas('purchased_plan.amount', 5900)
            ->assertSessionHas('purchase_confirmation_email', 'buyer@example.com');

        $this->get(route('home'))->assertOk();

        $this->postJson(route('purchase.confirmation.resend'))
            ->assertOk()
            ->assertJsonPath('ok', true);

        Mail::assertSent(PurchaseConfirmationMail::class, 1);
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

        $response = $this->from(route('home'))->beginPhoneRegistration();

        $response->assertRedirect(route('home'))
            ->assertSessionHas('waitlist_error', 'Non siamo riusciti a completare l’iscrizione. Riprova tra qualche minuto.');
    }

    public function test_waitlist_honeypot_blocks_spam_submission(): void
    {
        $response = $this->from(route('home'))
            ->post(route('waitlist.store'), [
                'phone_prefix' => '+39',
                'phone_number' => '3331234567',
                'website' => 'https://spam.example',
            ]);

        $response->assertRedirect(route('home'))
            ->assertSessionHas('waitlist_error', 'Non siamo riusciti a completare l’iscrizione. Riprova tra qualche minuto.');

        $this->assertDatabaseMissing('waitlist_entries', [
            'email' => 'person@example.com',
        ]);
    }
}
