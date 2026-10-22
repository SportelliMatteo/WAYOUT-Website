<?php

namespace App\Support;

use Illuminate\Support\Facades\Cache;
use InvalidArgumentException;

class FounderPromoCatalog
{
    public const JOIN_CODE = 'FOUNDER_JOIN_12M_PASS';

    public const CREATOR_CODE = 'FOUNDER_CREATOR_12M_PASS';

    public function __construct(private readonly WayoutApiClient $client) {}

    /** @return array{join: ?array, creator: ?array} */
    public function founderPackages(bool $fresh = false): array
    {
        $packages = $this->packages($fresh);

        return [
            'join' => $packages[self::JOIN_CODE] ?? null,
            'creator' => $packages[self::CREATOR_CODE] ?? null,
        ];
    }

    /** All paid packages exposed by the internal sales catalog, keyed by code. */
    public function packages(bool $fresh = false): array
    {
        return $fresh ? $this->load() : Cache::remember(
            'wayout:paid-promo-catalog:'.sha1((string) config('services.wayout.base_url')),
            max(1, (int) config('services.wayout.catalog_cache_seconds', 60)),
            fn (): array => $this->load(),
        );
    }

    public function packageForPlan(string $plan, bool $fresh = false): ?array
    {
        // Keep existing orders and integrations compatible with the former aliases.
        $code = match ($plan) {
            'join' => self::JOIN_CODE,
            'creator' => self::CREATOR_CODE,
            default => $plan,
        };

        return $this->packages($fresh)[$code] ?? null;
    }

    public function amountInCents(array $package): int
    {
        $price = (string) ($package['price'] ?? '');

        if (preg_match('/^\d+(?:\.\d{2})$/', $price) !== 1) {
            throw new InvalidArgumentException('Prezzo promo non valido.');
        }

        [$units, $cents] = array_pad(explode('.', $price, 2), 2, '00');

        return ((int) $units * 100) + (int) $cents;
    }

    /** @return array<string, array> */
    private function load(): array
    {
        $response = $this->client->internalGet('/api/v1/internal/promo-packages');
        $packages = [];

        foreach (($response['data'] ?? []) as $package) {
            if (! is_array($package)
                || ($package['free'] ?? true) !== false
                || ! is_string($package['code'] ?? null)
                || $package['code'] === ''
                || strlen($package['code']) > 80
                || ($package['active'] ?? true) !== true) {
                continue;
            }

            $packages[$package['code']] = $package;
        }

        return $packages;
    }
}
