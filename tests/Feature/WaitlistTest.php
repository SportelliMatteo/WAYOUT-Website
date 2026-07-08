<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
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
