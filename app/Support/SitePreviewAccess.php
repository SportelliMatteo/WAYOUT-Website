<?php

namespace App\Support;

use App\Models\AdminUser;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

final class SitePreviewAccess
{
    private const SESSION_KEY = 'admin_site_preview';

    private const DURATION_MINUTES = 120;

    public function enable(Request $request): Carbon
    {
        /** @var AdminUser|null $admin */
        $admin = $request->attributes->get('admin_user');
        abort_unless($admin && $admin->is_active && $admin->totp_confirmed_at, 403);

        $expiresAt = now()->addMinutes(self::DURATION_MINUTES);
        $request->session()->put(self::SESSION_KEY, [
            'admin_id' => (string) $admin->getKey(),
            'auth_version' => (string) $admin->auth_version,
            'expires_at' => $expiresAt->timestamp,
        ]);

        return $expiresAt;
    }

    public function disable(Request $request): void
    {
        $request->session()->forget(self::SESSION_KEY);
    }

    public function expiresAt(Request $request): ?Carbon
    {
        $preview = $request->session()->get(self::SESSION_KEY);

        if (! is_array($preview)
            || $request->session()->get('admin_authenticated') !== true
            || ! is_scalar($preview['admin_id'] ?? null)
            || ! is_scalar($preview['auth_version'] ?? null)
            || ! is_numeric($preview['expires_at'] ?? null)
            || (int) $preview['expires_at'] <= now()->timestamp
            || ! hash_equals((string) $preview['admin_id'], (string) $request->session()->get('admin_user_id'))
            || ! hash_equals((string) $preview['auth_version'], (string) $request->session()->get('admin_auth_version'))) {
            $this->disable($request);

            return null;
        }

        $admin = AdminUser::query()
            ->whereKey((string) $preview['admin_id'])
            ->where('is_active', true)
            ->whereNotNull('totp_confirmed_at')
            ->first();

        if (! $admin || ! hash_equals((string) $admin->auth_version, (string) $preview['auth_version'])) {
            $this->disable($request);

            return null;
        }

        return Carbon::createFromTimestamp((int) $preview['expires_at']);
    }

    public function isActive(Request $request): bool
    {
        return $this->expiresAt($request) !== null;
    }
}
