<?php

namespace Tests\Feature;

use App\Models\AdminUser;
use App\Support\DatabaseUuid;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class WaitlistDashboardTest extends TestCase
{
    use RefreshDatabase;

    private function adminSession(): array
    {
        $admin = AdminUser::create([
            'name' => 'Admin', 'email' => 'waitlist-admin@example.com', 'password' => Hash::make('Testing-password-123'),
            'is_active' => true, 'totp_confirmed_at' => now(),
        ]);

        return ['admin_authenticated' => true, 'admin_user_id' => $admin->id, 'admin_auth_version' => $admin->auth_version];
    }

    private function entries(): object
    {
        $eligible = $this->createVerifiedWaitlistEntry('eligible@example.com', 1);
        $buyer = $this->createVerifiedWaitlistEntry('buyer@example.com', 2);
        DatabaseUuid::insert('waitlist_entries', [
            'email' => 'pending@example.com', 'marketing_consent' => false, 'created_at' => now(), 'updated_at' => now(),
        ]);
        DatabaseUuid::insert('purchases', [
            'waitlist_entry_id' => $buyer->id, 'email' => $buyer->email, 'plan' => 'join', 'amount' => 2900,
            'currency' => 'eur', 'status' => 'succeeded', 'created_at' => now(), 'updated_at' => now(),
        ]);

        return $eligible;
    }

    public function test_dashboard_lists_waitlist_and_global_eligibility_counts_without_redemption_status(): void
    {
        $eligible = $this->entries();
        $this->withSession($this->adminSession())->get('/admin')->assertOk()
            ->assertSee('Utenti in waitlist')->assertSee('eligible@example.com')->assertSee('buyer@example.com')->assertSee('pending@example.com')
            ->assertSee($eligible->benefit_id)->assertSee('Abilitato · 60 giorni')
            ->assertViewHas('waitlistStats', ['total' => 3, 'verified' => 2, 'pending' => 1, 'eligible' => 1])
            ->assertDontSee('Riscattato')->assertDontSee('Da riscattare');
    }

    public function test_filters_use_the_same_eligibility_rule_as_the_api_and_do_not_change_totals(): void
    {
        $eligible = $this->entries();
        $this->withSession($this->adminSession())->get('/admin?waitlist_status=eligible')->assertOk()
            ->assertSee('eligible@example.com')->assertDontSee('buyer@example.com')->assertDontSee('pending@example.com')
            ->assertViewHas('waitlistStats', ['total' => 3, 'verified' => 2, 'pending' => 1, 'eligible' => 1]);
        $this->get('/admin?waitlist_q='.$eligible->benefit_id)->assertOk()->assertSee('eligible@example.com')->assertDontSee('buyer@example.com');
        $this->get('/admin?waitlist_status=pending')->assertOk()->assertSee('pending@example.com')->assertDontSee('eligible@example.com');
    }

    public function test_search_and_pagination_preserve_filters_and_require_admin_authentication(): void
    {
        $this->get('/admin?tab=waitlist')->assertRedirect(route('admin.login'));
        for ($position = 1; $position <= 6; $position++) {
            $this->createVerifiedWaitlistEntry('member'.$position.'@example.com', $position);
        }
        $this->withSession($this->adminSession())->get('/admin?waitlist_q=member&waitlist_status=eligible&waitlist_per_page=5')
            ->assertOk()->assertSee('waitlist_page=2')->assertSee('waitlist_status=eligible')
            ->assertViewHas('waitlistEntries', fn ($entries) => $entries->count() === 5 && $entries->total() === 6);
        $this->assertSame(6, DB::table('waitlist_entries')->count());
    }
}
