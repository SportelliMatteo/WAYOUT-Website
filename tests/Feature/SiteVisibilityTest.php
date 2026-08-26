<?php

namespace Tests\Feature;

use App\Models\AdminUser;
use App\Support\SiteVisibility;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class SiteVisibilityTest extends TestCase
{
    use RefreshDatabase;

    public function test_site_is_public_by_default(): void
    {
        $this->get(route('home'))
            ->assertOk()
            ->assertDontSee('Qualcosa di nuovo sta arrivando.');
    }

    public function test_coming_soon_replaces_public_pages_but_keeps_admin_accessible(): void
    {
        $this->setMode(1);

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('Qualcosa di nuovo sta arrivando.')
            ->assertHeader('X-Robots-Tag', 'noindex, nofollow, noarchive');

        $this->get(route('contact'))
            ->assertOk()
            ->assertSee('Qualcosa di nuovo sta arrivando.')
            ->assertSee('border-slate-300 bg-slate-100 text-slate-700', false);

        $this->get(route('home', ['lang' => 'en']))
            ->assertOk()
            ->assertSee('Something new is on its way.')
            ->assertSee('border-slate-950 bg-slate-950 text-white', false);

        $this->get(route('admin.login'))
            ->assertOk()
            ->assertDontSee('Qualcosa di nuovo sta arrivando.');
    }

    public function test_maintenance_returns_a_service_unavailable_response(): void
    {
        $this->setMode(2);

        $this->get(route('home'))
            ->assertStatus(503)
            ->assertSee('Torniamo tra poco.')
            ->assertHeader('Retry-After', '3600');
    }

    public function test_admin_can_change_site_visibility_and_change_is_audited(): void
    {
        $admin = AdminUser::query()->create([
            'name' => 'Test Admin',
            'email' => 'admin@example.com',
            'password' => Hash::make('Testing-password-123'),
            'is_active' => true,
            'totp_confirmed_at' => now(),
        ]);

        $session = [
            'admin_authenticated' => true,
            'admin_user_id' => $admin->id,
            'admin_auth_version' => $admin->auth_version,
        ];

        $this->withSession($session)
            ->post(route('admin.site-visibility.update'), ['mode' => SiteVisibility::COMING_SOON])
            ->assertRedirect(route('admin.dashboard', ['tab' => 'settings']))
            ->assertSessionHas('admin_success', 'Visibilità del sito aggiornata.');

        $this->assertSame(SiteVisibility::COMING_SOON, app(SiteVisibility::class)->mode());
        $this->assertDatabaseHas('admin_audit_events', [
            'actor_email' => 'admin@example.com',
            'action' => 'site_visibility.updated',
            'target_type' => 'site_settings',
        ]);
    }

    private function setMode(int $value): void
    {
        DB::table('founder_settings')
            ->where('key', SiteVisibility::SETTING_KEY)
            ->update(['value' => $value, 'updated_at' => now()]);
    }
}
