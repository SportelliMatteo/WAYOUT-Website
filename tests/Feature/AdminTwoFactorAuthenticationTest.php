<?php

namespace Tests\Feature;

use App\Models\AdminUser;
use App\Support\TotpService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AdminTwoFactorAuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_password_login_requires_totp_before_dashboard_access(): void
    {
        $admin = $this->admin();

        $this->post(route('admin.authenticate'), [
            'email' => $admin->email,
            'password' => 'Testing-password-123',
        ])->assertRedirect(route('admin.otp.challenge'));

        $this->assertFalse(session()->has('admin_authenticated'));
        $this->get(route('admin.dashboard'))->assertRedirect(route('admin.login'));

        $this->withSession([
            'admin_pending_id' => $admin->id,
            'admin_pending_expires_at' => now()->addMinutes(5)->timestamp,
        ])->get(route('admin.otp.setup'))->assertRedirect(route('admin.otp.challenge'));

        $code = $this->currentTotpCode($admin->totp_secret);

        $this->withSession([
            'admin_pending_id' => $admin->id,
            'admin_pending_expires_at' => now()->addMinutes(5)->timestamp,
        ])->post(route('admin.otp.verify'), ['code' => $code])
            ->assertRedirect(route('admin.dashboard'));

        $this->assertTrue(session('admin_authenticated'));
        $this->get(route('admin.dashboard'))->assertOk();
    }

    public function test_first_totp_setup_encrypts_secret_and_shows_recovery_codes_once(): void
    {
        $admin = $this->admin(['totp_secret' => null, 'totp_confirmed_at' => null]);
        $pending = [
            'admin_pending_id' => $admin->id,
            'admin_pending_expires_at' => now()->addMinutes(5)->timestamp,
        ];

        $this->withSession($pending)->get(route('admin.otp.setup'))
            ->assertOk()
            ->assertSee('<svg', false)
            ->assertSee('Configura Authenticator');

        $admin->refresh();
        $this->assertNotSame($admin->totp_secret, DB::table('admin_users')->where('id', $admin->id)->value('totp_secret'));

        $this->withSession($pending)->post(route('admin.otp.setup.confirm'), [
            'code' => $this->currentTotpCode($admin->totp_secret),
        ])->assertRedirect(route('admin.recovery-codes'));

        $admin->refresh();
        $this->assertNotNull($admin->totp_confirmed_at);
        $this->assertCount(10, $admin->recovery_codes);
        $this->assertTrue(session()->has('admin_encrypted_recovery_codes'));

        $this->post(route('admin.recovery-codes.acknowledge'))
            ->assertRedirect(route('admin.dashboard'));
        $this->assertFalse(session()->has('admin_encrypted_recovery_codes'));
        $this->get(route('admin.recovery-codes'))->assertNotFound();
    }

    public function test_recovery_code_is_single_use_and_forces_new_totp_setup(): void
    {
        $totp = app(TotpService::class);
        $recovery = $totp->generateRecoveryCodes();
        $admin = $this->admin(['recovery_codes' => $recovery['hashes']]);

        $this->withSession([
            'admin_pending_id' => $admin->id,
            'admin_pending_expires_at' => now()->addMinutes(5)->timestamp,
        ])->post(route('admin.otp.verify'), ['code' => $recovery['plain'][0]])
            ->assertRedirect(route('admin.otp.setup'));

        $admin->refresh();
        $this->assertNull($admin->totp_secret);
        $this->assertNull($admin->totp_confirmed_at);
        $this->assertCount(9, $admin->recovery_codes);
        $this->assertFalse(session()->has('admin_authenticated'));
    }

    public function test_admin_can_reset_the_password_from_the_login_screen_with_a_recovery_code(): void
    {
        $totp = app(TotpService::class);
        $recovery = $totp->generateRecoveryCodes();
        $admin = $this->admin(['recovery_codes' => $recovery['hashes']]);
        $previousAuthVersion = $admin->auth_version;

        $this->get(route('admin.login'))
            ->assertOk()
            ->assertSee('Hai dimenticato la password?');

        $this->post(route('admin.recover'), [
            'email' => $admin->email,
            'recovery_code' => strtolower(str_replace('-', ' ', $recovery['plain'][0])),
            'password' => 'New-testing-password-456',
            'password_confirmation' => 'New-testing-password-456',
            'recovery_mode' => '1',
        ])->assertRedirect(route('admin.login'))
            ->assertSessionHas('admin_recovery_success');

        $admin->refresh();
        $this->assertTrue(Hash::check('New-testing-password-456', $admin->password));
        $this->assertNotNull($admin->totp_secret);
        $this->assertNotNull($admin->totp_confirmed_at);
        $this->assertCount(9, $admin->recovery_codes);
        $this->assertSame($previousAuthVersion + 1, $admin->auth_version);
        $this->assertFalse(session()->has('admin_authenticated'));
        $this->assertFalse(session()->has('admin_pending_id'));

        $this->post(route('admin.recover'), [
            'email' => $admin->email,
            'recovery_code' => $recovery['plain'][0],
            'password' => 'Another-password-789',
            'password_confirmation' => 'Another-password-789',
            'recovery_mode' => '1',
        ])->assertSessionHasErrors('recovery_code');
    }

    public function test_every_admin_can_create_accounts_up_to_the_shared_limit_of_four(): void
    {
        $creator = $this->admin(['email' => 'creator@example.com']);

        foreach (range(2, 4) as $number) {
            $this->withSession($this->authenticatedSession($creator))
                ->post(route('admin.users.store'), [
                    'name' => 'Admin '.$number,
                    'email' => 'admin'.$number.'@example.com',
                    'password' => 'Strong-password-'.$number,
                    'password_confirmation' => 'Strong-password-'.$number,
                ])->assertRedirect(route('admin.dashboard', ['tab' => 'settings']));
        }

        $this->assertSame(4, AdminUser::query()->count());

        $response = $this->withSession($this->authenticatedSession($creator))
            ->post(route('admin.users.store'), [
                'name' => 'Admin 5',
                'email' => 'admin5@example.com',
                'password' => 'Strong-password-5',
                'password_confirmation' => 'Strong-password-5',
            ]);

        $response->assertRedirect();
        $this->assertSame(4, AdminUser::query()->count());
        $this->assertDatabaseMissing('admin_users', ['email' => 'admin5@example.com']);
    }

    public function test_dashboard_does_not_expose_routes_to_manage_other_admins(): void
    {
        $routes = app('router')->getRoutes();

        $this->assertNull($routes->getByName('admin.users.reset-otp'));
        $this->assertNull($routes->getByName('admin.users.toggle'));
        $this->assertNull($routes->getByName('admin.users.destroy'));
    }

    public function test_admin_can_change_only_own_password_and_other_sessions_are_invalidated(): void
    {
        $admin = $this->admin();
        $oldAuthVersion = $admin->auth_version;

        $this->withSession($this->authenticatedSession($admin))
            ->post(route('admin.password.change'), [
                'current_password' => 'Testing-password-123',
                'password' => 'New-testing-password-456',
                'password_confirmation' => 'New-testing-password-456',
            ])->assertRedirect(route('admin.dashboard', ['tab' => 'settings']))
            ->assertSessionHas('admin_success');

        $admin->refresh();
        $this->assertTrue(Hash::check('New-testing-password-456', $admin->password));
        $this->assertFalse(Hash::check('Testing-password-123', $admin->password));
        $this->assertSame($oldAuthVersion + 1, $admin->auth_version);
        $this->assertSame($admin->auth_version, session('admin_auth_version'));
        $this->get(route('admin.dashboard'))->assertOk();

        $this->withSession([
            'admin_authenticated' => true,
            'admin_user_id' => $admin->id,
            'admin_auth_version' => $oldAuthVersion,
        ])->get(route('admin.dashboard'))->assertRedirect(route('admin.login'));

        $this->assertDatabaseHas('admin_audit_events', [
            'admin_user_id' => $admin->id,
            'actor_email' => $admin->email,
            'action' => 'admin_user.password_changed',
        ]);
    }

    public function test_console_can_reset_one_admin_or_all_admins(): void
    {
        $first = $this->admin(['email' => 'first@example.com']);
        $second = $this->admin(['email' => 'second@example.com']);
        $firstVersion = $first->auth_version;
        $secondVersion = $second->auth_version;

        $this->artisan('admin:reset-otp', ['email' => $first->email, '--force' => true])
            ->assertSuccessful();

        $first->refresh();
        $second->refresh();
        $this->assertNull($first->totp_secret);
        $this->assertSame($firstVersion + 1, $first->auth_version);
        $this->assertNotNull($second->totp_secret);
        $this->assertSame($secondVersion, $second->auth_version);

        $this->artisan('admin:reset-otp', ['--all' => true, '--force' => true])
            ->assertSuccessful();

        $second->refresh();
        $this->assertNull($second->totp_secret);
        $this->assertSame($secondVersion + 1, $second->auth_version);
        $this->assertDatabaseHas('admin_audit_events', [
            'actor_email' => 'console@localhost',
            'action' => 'admin_user.otp_reset_console',
            'target_label' => 'second@example.com',
        ]);
    }

    private function admin(array $overrides = []): AdminUser
    {
        return AdminUser::query()->create(array_merge([
            'name' => 'Test Admin',
            'email' => 'admin@example.com',
            'password' => Hash::make('Testing-password-123'),
            'totp_secret' => 'JBSWY3DPEHPK3PXP',
            'totp_confirmed_at' => now(),
            'recovery_codes' => [],
            'is_active' => true,
        ], $overrides));
    }

    private function authenticatedSession(AdminUser $admin): array
    {
        return [
            'admin_authenticated' => true,
            'admin_user_id' => $admin->id,
            'admin_auth_version' => $admin->auth_version,
        ];
    }

    private function currentTotpCode(string $secret): string
    {
        $alphabet = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';
        $bits = '';

        foreach (str_split($secret) as $character) {
            $bits .= str_pad(decbin((int) strpos($alphabet, $character)), 5, '0', STR_PAD_LEFT);
        }

        $key = '';
        foreach (str_split($bits, 8) as $chunk) {
            if (strlen($chunk) === 8) {
                $key .= chr(bindec($chunk));
            }
        }

        $step = intdiv(time(), 30);
        $counter = pack('N2', ($step >> 32) & 0xFFFFFFFF, $step & 0xFFFFFFFF);
        $hash = hash_hmac('sha1', $counter, $key, true);
        $offset = ord($hash[19]) & 0x0F;
        $binary = ((ord($hash[$offset]) & 0x7F) << 24)
            | ((ord($hash[$offset + 1]) & 0xFF) << 16)
            | ((ord($hash[$offset + 2]) & 0xFF) << 8)
            | (ord($hash[$offset + 3]) & 0xFF);

        return str_pad((string) ($binary % 1_000_000), 6, '0', STR_PAD_LEFT);
    }
}
