<?php

namespace App\Http\Controllers;

use App\Models\AdminUser;
use App\Support\AdminAuditService;
use App\Support\TotpService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;

class AdminAuthController extends Controller
{
    public function login(Request $request)
    {
        if ($request->session()->get('admin_authenticated')) {
            return redirect()->route('admin.dashboard');
        }

        return view('admin.login');
    }

    public function authenticate(Request $request)
    {
        $validated = $request->validate([
            'email' => ['required', 'email:rfc'],
            'password' => ['required', 'string'],
        ]);

        $email = Str::lower($validated['email']);
        $admin = Schema::hasTable('admin_users')
            ? AdminUser::query()->where('email', $email)->first()
            : null;

        if (! $admin && Schema::hasTable('admin_users') && AdminUser::query()->count() === 0) {
            $admin = $this->bootstrapAdmin($email, $validated['password']);
        }

        if (! $admin || ! $admin->is_active || ! Hash::check($validated['password'], $admin->password)) {
            Log::warning('Admin password authentication failed.', [
                'email' => $email,
                'ip_address' => $request->ip(),
            ]);

            throw ValidationException::withMessages([
                'email' => __('messages.messages.admin_invalid_credentials'),
            ]);
        }

        $request->session()->invalidate();
        $request->session()->regenerateToken();
        $request->session()->put([
            'admin_pending_id' => $admin->id,
            'admin_pending_expires_at' => now()->addMinutes(5)->timestamp,
        ]);

        return redirect()->route($admin->totp_confirmed_at ? 'admin.otp.challenge' : 'admin.otp.setup');
    }

    public function showSetup(Request $request, TotpService $totp)
    {
        $admin = $this->pendingAdmin($request);

        if ($admin->totp_confirmed_at) {
            return redirect()->route('admin.otp.challenge');
        }

        if (! $admin->totp_secret) {
            $admin->forceFill(['totp_secret' => $totp->generateSecret()])->save();
        }

        $uri = $totp->provisioningUri($admin->totp_secret, $admin->email);

        return view('admin.otp-setup', [
            'admin' => $admin,
            'secret' => $admin->totp_secret,
            'qrSvg' => $totp->qrSvg($uri),
        ]);
    }

    public function confirmSetup(Request $request, TotpService $totp)
    {
        $admin = $this->pendingAdmin($request);

        if ($admin->totp_confirmed_at) {
            return redirect()->route('admin.otp.challenge');
        }

        $validated = $request->validate(['code' => ['required', 'digits:6']]);
        $step = $admin->totp_secret ? $totp->verify($admin->totp_secret, $validated['code']) : null;

        if ($step === null) {
            throw ValidationException::withMessages(['code' => __('messages.admin.otp_invalid')]);
        }

        $recovery = $totp->generateRecoveryCodes();
        $admin->forceFill([
            'recovery_codes' => $recovery['hashes'],
            'last_totp_step' => $step,
            'totp_confirmed_at' => now(),
            'last_login_at' => now(),
        ])->save();

        $this->completeLogin($request, $admin);
        $request->session()->put(
            'admin_encrypted_recovery_codes',
            Crypt::encryptString(json_encode($recovery['plain'], JSON_THROW_ON_ERROR)),
        );

        Log::info('Admin TOTP activated.', ['admin_user_id' => $admin->id, 'email' => $admin->email]);

        return redirect()->route('admin.recovery-codes');
    }

    public function showChallenge(Request $request)
    {
        $admin = $this->pendingAdmin($request);

        if (! $admin->totp_confirmed_at) {
            return redirect()->route('admin.otp.setup');
        }

        return view('admin.otp-challenge', ['admin' => $admin]);
    }

    public function verifyChallenge(Request $request, TotpService $totp)
    {
        $admin = $this->pendingAdmin($request);

        if (! $admin->totp_confirmed_at) {
            return redirect()->route('admin.otp.setup');
        }

        $validated = $request->validate(['code' => ['required', 'string', 'max:32']]);
        $step = $admin->totp_secret
            ? $totp->verify($admin->totp_secret, $validated['code'], $admin->last_totp_step)
            : null;

        if ($step !== null) {
            $admin->forceFill(['last_totp_step' => $step, 'last_login_at' => now()])->save();
            $this->completeLogin($request, $admin);
            Log::info('Admin TOTP authentication succeeded.', ['admin_user_id' => $admin->id]);

            return redirect()->intended(route('admin.dashboard'));
        }

        $remainingCodes = $totp->consumeRecoveryCode($validated['code'], $admin->recovery_codes ?? []);

        if ($remainingCodes !== null) {
            $admin->forceFill([
                'totp_secret' => null,
                'totp_confirmed_at' => null,
                'last_totp_step' => null,
                'recovery_codes' => $remainingCodes,
                'auth_version' => $admin->auth_version + 1,
            ])->save();

            $request->session()->put([
                'admin_pending_id' => $admin->id,
                'admin_pending_expires_at' => now()->addMinutes(5)->timestamp,
            ]);

            Log::warning('Admin recovery code used; TOTP reconfiguration required.', ['admin_user_id' => $admin->id]);

            return redirect()->route('admin.otp.setup');
        }

        Log::warning('Admin OTP authentication failed.', [
            'admin_user_id' => $admin->id,
            'ip_address' => $request->ip(),
        ]);

        throw ValidationException::withMessages(['code' => __('messages.admin.otp_invalid')]);
    }

    public function recoveryCodes(Request $request)
    {
        $encrypted = $request->session()->get('admin_encrypted_recovery_codes');
        abort_unless(is_string($encrypted), 404);

        return view('admin.recovery-codes', [
            'codes' => json_decode(Crypt::decryptString($encrypted), true, flags: JSON_THROW_ON_ERROR),
        ]);
    }

    public function acknowledgeRecoveryCodes(Request $request)
    {
        $request->session()->forget('admin_encrypted_recovery_codes');

        return redirect()->route('admin.dashboard');
    }

    public function changePassword(Request $request, AdminAuditService $audit)
    {
        /** @var AdminUser $admin */
        $admin = $request->attributes->get('admin_user');
        $validated = $request->validate([
            'current_password' => ['required', 'string'],
            'password' => ['required', 'confirmed', Password::min(12)->letters()->mixedCase()->numbers()],
        ]);

        if (! Hash::check($validated['current_password'], $admin->password)) {
            throw ValidationException::withMessages([
                'current_password' => __('messages.admin.password_invalid'),
            ]);
        }

        if (Hash::check($validated['password'], $admin->password)) {
            throw ValidationException::withMessages([
                'password' => __('messages.admin.password_must_change'),
            ]);
        }

        $previousAuthVersion = $admin->auth_version;

        DB::transaction(function () use ($request, $validated, $admin, $audit, $previousAuthVersion) {
            $admin->forceFill([
                'password' => Hash::make($validated['password']),
                'auth_version' => $previousAuthVersion + 1,
            ])->save();

            $audit->record(
                $request,
                'admin_user.password_changed',
                'admin_user',
                $admin->id,
                $admin->email,
                oldValues: ['auth_version' => $previousAuthVersion],
                newValues: ['auth_version' => $admin->auth_version],
            );
        });

        $request->session()->regenerate();
        $request->session()->regenerateToken();
        $request->session()->put('admin_auth_version', $admin->auth_version);

        Log::info('Admin changed own password.', ['admin_user_id' => $admin->id]);

        return redirect()->route('admin.dashboard', ['tab' => 'settings'])
            ->with('admin_success', __('messages.admin.password_changed'));
    }

    public function resetOwnOtp(Request $request, TotpService $totp, AdminAuditService $audit)
    {
        /** @var AdminUser $admin */
        $admin = $request->attributes->get('admin_user');
        $validated = $request->validate([
            'password' => ['required', 'string'],
            'code' => ['required', 'digits:6'],
        ]);

        if (! Hash::check($validated['password'], $admin->password)) {
            throw ValidationException::withMessages(['password' => __('messages.admin.password_invalid')]);
        }

        $step = $admin->totp_secret
            ? $totp->verify($admin->totp_secret, $validated['code'], $admin->last_totp_step)
            : null;

        if ($step === null) {
            throw ValidationException::withMessages(['code' => __('messages.admin.otp_invalid_new_code')]);
        }

        $this->clearOtp($admin);
        $audit->record(
            $request,
            'admin_user.otp_reset_self',
            'admin_user',
            $admin->id,
            $admin->email,
            oldValues: ['totp_configured' => true],
            newValues: ['totp_configured' => false],
        );
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        $request->session()->put([
            'admin_pending_id' => $admin->id,
            'admin_pending_expires_at' => now()->addMinutes(5)->timestamp,
        ]);

        Log::warning('Admin reset own TOTP.', ['admin_user_id' => $admin->id]);

        return redirect()->route('admin.otp.setup');
    }

    public function logout(Request $request)
    {
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('admin.login');
    }

    private function bootstrapAdmin(string $email, string $password): ?AdminUser
    {
        $configuredEmail = Str::lower((string) config('admin.email'));
        $configuredPassword = (string) config('admin.password');
        $passwordMatches = Str::startsWith($configuredPassword, ['$2y$', '$argon2id$', '$argon2i$'])
            ? Hash::check($password, $configuredPassword)
            : ($configuredPassword !== '' && hash_equals($configuredPassword, $password));

        if ($configuredEmail === '' || ! hash_equals($configuredEmail, $email) || ! $passwordMatches) {
            return null;
        }

        return AdminUser::query()->create([
            'name' => 'Admin',
            'email' => $email,
            'password' => Hash::make($password),
            'is_active' => true,
        ]);
    }

    private function pendingAdmin(Request $request): AdminUser
    {
        $id = $request->session()->get('admin_pending_id');
        $expiresAt = (int) $request->session()->get('admin_pending_expires_at', 0);
        $admin = $id && $expiresAt >= now()->timestamp
            ? AdminUser::query()->whereKey($id)->where('is_active', true)->first()
            : null;

        if (! $admin) {
            $request->session()->forget(['admin_pending_id', 'admin_pending_expires_at']);
            abort(403, 'Sessione di autenticazione scaduta.');
        }

        return $admin;
    }

    private function completeLogin(Request $request, AdminUser $admin): void
    {
        $request->session()->regenerate();
        $request->session()->forget(['admin_pending_id', 'admin_pending_expires_at']);
        $request->session()->put([
            'admin_authenticated' => true,
            'admin_user_id' => $admin->id,
            'admin_auth_version' => $admin->auth_version,
        ]);
    }

    private function clearOtp(AdminUser $admin): void
    {
        $admin->forceFill([
            'totp_secret' => null,
            'totp_confirmed_at' => null,
            'last_totp_step' => null,
            'recovery_codes' => null,
            'auth_version' => $admin->auth_version + 1,
        ])->save();
    }
}
