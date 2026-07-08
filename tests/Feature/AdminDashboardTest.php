<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class AdminDashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_dashboard_requires_login(): void
    {
        $response = $this->get(route('admin.dashboard'));

        $response->assertRedirect(route('admin.login'));
    }

    public function test_admin_can_login_with_configured_credentials(): void
    {
        config()->set('admin.email', 'admin@wayout.test');
        config()->set('admin.password', 'secret-password');

        $response = $this->post(route('admin.authenticate'), [
            'email' => 'admin@wayout.test',
            'password' => 'secret-password',
        ]);

        $response->assertRedirect(route('admin.dashboard'));
        $this->assertTrue(session('admin_authenticated'));
    }

    public function test_admin_dashboard_shows_waitlist_purchase_and_revenue_totals(): void
    {
        $this->seedDashboardData();

        $response = $this->withSession(['admin_authenticated' => true])
            ->get(route('admin.dashboard'));

        $response->assertOk()
            ->assertSee('Waitlist e ordini')
            ->assertSee('buyer@example.com')
            ->assertSee('Founder Creator 12M')
            ->assertSee('88,00€')
            ->assertSee('29,00€')
            ->assertSee('59,00€')
            ->assertSee('Capienze Founder')
            ->assertSee('Utenti Founder Join 12M')
            ->assertSee('Utenti Founder Creator 12M');
    }

    public function test_admin_can_update_founder_capacities(): void
    {
        $response = $this->withSession(['admin_authenticated' => true])
            ->post(route('admin.settings.update'), [
                'waitlist_capacity' => 2500,
                'join_capacity' => 700,
                'creator_capacity' => 220,
            ]);

        $response->assertRedirect()
            ->assertSessionHas('admin_success', 'Dati aggiornati.');

        $this->assertDatabaseHas('founder_settings', [
            'key' => 'waitlist_capacity',
            'value' => 2500,
        ]);

        $this->assertDatabaseHas('founder_settings', [
            'key' => 'join_capacity',
            'value' => 700,
        ]);

        $this->assertDatabaseHas('founder_settings', [
            'key' => 'creator_capacity',
            'value' => 220,
        ]);
    }

    public function test_admin_dashboard_can_filter_waitlist_buyers(): void
    {
        $this->seedDashboardData();

        $response = $this->withSession(['admin_authenticated' => true])
            ->get(route('admin.dashboard', ['status' => 'buyers']));

        $response->assertOk()
            ->assertSee('buyer@example.com')
            ->assertDontSee('lead@example.com');
    }

    private function seedDashboardData(): void
    {
        DB::table('waitlist_entries')->insert([
            [
                'email' => 'buyer@example.com',
                'offer_shown' => true,
                'created_at' => now()->subDays(2),
                'updated_at' => now()->subDays(2),
            ],
            [
                'email' => 'lead@example.com',
                'offer_shown' => true,
                'created_at' => now()->subDay(),
                'updated_at' => now()->subDay(),
            ],
        ]);

        DB::table('purchases')->insert([
            [
                'email' => 'buyer@example.com',
                'plan' => 'join',
                'amount' => 2900,
                'currency' => 'eur',
                'stripe_session_id' => 'direct_join',
                'status' => 'succeeded',
                'created_at' => now()->subHours(3),
                'updated_at' => now()->subHours(3),
            ],
            [
                'email' => 'buyer@example.com',
                'plan' => 'creator',
                'amount' => 5900,
                'currency' => 'eur',
                'stripe_session_id' => 'direct_creator',
                'status' => 'succeeded',
                'created_at' => now()->subHours(2),
                'updated_at' => now()->subHours(2),
            ],
        ]);
    }
}
