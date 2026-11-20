<?php

namespace App\Support;

use App\Exceptions\WayoutApiException;
use Illuminate\Http\Request;

class CustomerAccount
{
    public function __construct(private readonly WayoutApiClient $client) {}

    public function eligibility(Request $request, array $selection): array
    {
        $response = $this->client->internalPost('/api/v1/internal/purchase-eligibility', [
            'user' => ['id' => $request->attributes->get('customer_profile')['id']],
            ...$selection,
        ]);
        $verdict = data_get($response, 'data');
        if (! is_array($verdict) || ! is_bool($verdict['allowed'] ?? null)) {
            throw new WayoutApiException('Idoneità acquisto non disponibile.', 502);
        }

        return $verdict;
    }

    public function state(Request $request): array
    {
        $profile = $request->attributes->get('customer_profile');
        $response = $this->client->internalGet('/api/v1/internal/users/'.$profile['id'].'/subscription');
        $data = data_get($response, 'data');
        if (! is_array($data) || ! is_bool($data['has_active_entitlement'] ?? null)
            || ! array_key_exists('subscription', $data) || ! array_key_exists('offer', $data)
            || ($data['subscription'] !== null && ! is_array($data['subscription']))
            || ($data['offer'] !== null && ! is_array($data['offer']))) {
            throw new WayoutApiException('Stato abbonamento non disponibile.', 502);
        }
        $subscription = $data['subscription'];
        $offer = $data['offer'];
        $source = $subscription['source'] ?? null;
        $managePortal = $source === 'STRIPE' && ($offer['type'] ?? null) === 'PLAN';
        $message = match (true) {
            $managePortal => __('account.stripe_manage'),
            $source === 'APPLE' => __('account.apple_manage'),
            $source === 'GOOGLE' => __('account.google_manage'),
            $subscription !== null => __('account.package_info'),
            default => null,
        };

        return compact('subscription', 'offer', 'managePortal', 'message');
    }

    public function history(Request $request): array
    {
        $response = $this->client->bearerGet('/api/v1/me/subscription/history', $request->attributes->get('customer_token'));
        $history = data_get($response, 'data');
        if (! is_array($history) || ! array_is_list($history)) {
            throw new WayoutApiException('Storico abbonamenti non disponibile.', 502);
        }
        foreach ($history as $entry) {
            if (! is_array($entry) || ! is_string($entry['source'] ?? null) || ! is_string($entry['status'] ?? null)) {
                throw new WayoutApiException('Storico abbonamenti non valido.', 502);
            }
        }

        return $history;
    }
}
