<?php

namespace App\Http\Middleware;

use App\Exceptions\WayoutApiException;
use App\Support\CustomerSession;
use App\Support\WayoutApiClient;
use Closure;
use Illuminate\Http\Request;

class RequireCustomerAuthentication
{
    public function handle(Request $request, Closure $next)
    {
        $token = app(CustomerSession::class)->token($request);
        if ($token) {
            try {
                $profile = data_get(app(WayoutApiClient::class)->bearerGet('/api/v1/users/me', $token), 'data');
                if (! is_array($profile) || ! is_string($profile['id'] ?? null)) {
                    throw new WayoutApiException('Profilo non disponibile.', 502);
                }
                $request->attributes->set('customer_token', $token);
                $request->attributes->set('customer_profile', $profile);
                $request->session()->put('customer.profile', $profile);

                return $next($request);
            } catch (WayoutApiException $exception) {
                if (! in_array($exception->status, [401, 403], true)) {
                    return $request->expectsJson()
                        ? response()->json(['error' => __('account.unavailable')], 503)
                        : response()->view('pages.account-error', [], 503);
                }
            }
        }
        $request->session()->forget('customer');

        return $request->expectsJson()
            ? response()->json(['error' => __('account.login_required'), 'redirectUrl' => route('customer.login')], 401)
            : redirect()->route('customer.login');
    }
}
