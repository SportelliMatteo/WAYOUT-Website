<?php

namespace App\Http\Controllers;

use App\Exceptions\WayoutApiException;
use App\Support\CustomerAccount;
use App\Support\CustomerSession;
use App\Support\WayoutApiClient;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Log;

class CustomerAccountController extends Controller
{
    public function login(Request $request, CustomerSession $session)
    {
        if ($session->token($request)) {
            return redirect()->route('customer.dashboard');
        }

        return view('pages.customer-login');
    }

    public function authenticate(Request $request, WayoutApiClient $client, CustomerSession $session)
    {
        $validated = $request->validate(['firebase_token' => ['required', 'string', 'max:10000']]);
        try {
            $response = $client->post('/api/auth/verify-firebase-token', $validated);
            $token = data_get($response->json(), 'data.access_token') ?? data_get($response->json(), 'access_token');
            if (is_string($token) && $token !== '') {
                $profile = data_get($client->bearerGet('/api/v1/users/me', $token), 'data');
                if (! is_array($profile) || ! is_string($profile['id'] ?? null)) {
                    throw new WayoutApiException('Profilo non disponibile.', 502);
                }
                $session->start($request, $token, $profile);

                return redirect()->route('customer.dashboard');
            }
            $tempToken = $response->header('X-Temp-Token') ?: data_get($response->json(), 'data.temp_token') ?: data_get($response->json(), 'temp_token');
            if (! is_string($tempToken) || $tempToken === '') {
                throw new WayoutApiException('Sessione di registrazione non disponibile.', 502);
            }
            $request->session()->forget(['customer', 'customer_registration']);
            $request->session()->regenerate();

            return redirect()->route('customer.login')->withErrors(['login' => __('account.app_account_required')]);
        } catch (WayoutApiException $exception) {
            Log::warning('Customer login failed.', $exception->logContext());

            $request->session()->forget(['customer', 'customer_registration']);
            $requiresAppAccount = $exception->status === 404 && $exception->apiCode === 'USER_NOT_FOUND';

            return redirect()->route('customer.login')->withErrors([
                'login' => __($requiresAppAccount ? 'account.app_account_required' : 'account.login_failed'),
            ]);
        }
    }

    public function dashboard(Request $request, CustomerAccount $account)
    {
        try {
            $state = $account->state($request);
            try {
                $state['history'] = $account->history($request);
                $total = count($state['history']);
                $requestedPage = filter_var($request->query('history_page', 1), FILTER_VALIDATE_INT);
                $page = min(max(1, $requestedPage ?: 1), max(1, (int) ceil($total / 4)));
                $state['historyPagination'] = (new LengthAwarePaginator(
                    array_slice($state['history'], ($page - 1) * 4, 4),
                    $total,
                    4,
                    $page,
                    ['path' => route('customer.dashboard'), 'pageName' => 'history_page', 'fragment' => 'subscription-history'],
                ))->appends($request->query());
                $state['historyError'] = false;
            } catch (WayoutApiException $exception) {
                Log::warning('Customer subscription history unavailable.', $exception->logContext());
                $state['history'] = [];
                $state['historyError'] = true;
            }
            $request->attributes->set('customer_account', $state);

            return app()->call([app(SubscribeController::class), 'show'], ['request' => $request]);
        } catch (WayoutApiException $exception) {
            Log::warning('Customer account unavailable.', $exception->logContext());

            return response()->view('pages.account-error', [], 503);
        }
    }

    public function portal(Request $request, WayoutApiClient $client)
    {
        try {
            $response = $client->internalPost('/api/v1/internal/billing-portal-sessions', [
                'user' => ['id' => $request->attributes->get('customer_profile')['id']],
                'return_url' => route('customer.dashboard'),
            ]);
            if (data_get($response, 'data.allowed') === false) {
                return redirect()->route('customer.dashboard')->with('account_error',
                    data_get($response, 'data.message') ?: __('account.portal_unavailable'));
            }
            $url = data_get($response, 'data.session.url');
            if (data_get($response, 'data.allowed') !== true || ! is_string($url)
                || parse_url($url, PHP_URL_SCHEME) !== 'https'
                || parse_url($url, PHP_URL_HOST) !== 'billing.stripe.com'
                || parse_url($url, PHP_URL_USER) !== null || parse_url($url, PHP_URL_PASS) !== null) {
                throw new WayoutApiException('Sessione del portale non valida.', 502);
            }

            return redirect()->away($url);
        } catch (WayoutApiException $exception) {
            Log::warning('Customer billing portal unavailable.', $exception->logContext());

            return redirect()->route('customer.dashboard')->with('account_error', __('account.portal_unavailable'));
        }
    }

    public function logout(Request $request)
    {
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('customer.login');
    }
}
