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
        $loader = fn (): array => $this->load();
        $packages = $fresh
            ? $loader()
            : Cache::remember(
                'wayout:founder-promo-catalog:'.sha1((string) config('services.wayout.base_url')),
                max(1, (int) config('services.wayout.catalog_cache_seconds', 60)),
                $loader,
            );

        return [
            'join' => $packages[self::JOIN_CODE] ?? null,
            'creator' => $packages[self::CREATOR_CODE] ?? null,
        ];
    }

    public function packageForPlan(string $plan, bool $fresh = false): ?array
    {
        if (! in_array($plan, ['join', 'creator'], true)) {
            throw new InvalidArgumentException('Piano Founder non valido.');
        }

        return $this->founderPackages($fresh)[$plan];
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
        $allowedCodes = [self::JOIN_CODE, self::CREATOR_CODE];
        $packages = [];

        foreach (($response['data'] ?? []) as $package) {
            if (! is_array($package)
                || ($package['free'] ?? true) !== false
                || ! in_array($package['code'] ?? null, $allowedCodes, true)) {
                continue;
            }

            $packages[$package['code']] = $package;
        }

        return $packages;
    }
}
