<?php

namespace App\Http\Middleware;

use App\Models\AdminUser;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RequireAdminAuthentication
{
    public function handle(Request $request, Closure $next): Response
    {
        $adminId = $request->session()->get('admin_user_id');
        $authVersion = $request->session()->get('admin_auth_version');

        $admin = $request->session()->get('admin_authenticated') === true && $adminId
            ? AdminUser::query()->whereKey($adminId)->where('is_active', true)->first()
            : null;

        if (! $admin || ! hash_equals((string) $admin->auth_version, (string) $authVersion)) {
            $request->session()->forget([
                'admin_authenticated',
                'admin_user_id',
                'admin_auth_version',
            ]);

            return redirect()->guest(route('admin.login'));
        }

        $request->attributes->set('admin_user', $admin);

        return $next($request);
    }
}
