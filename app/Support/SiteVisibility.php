<?php

namespace App\Support;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Throwable;

final class SiteVisibility
{
    public const ONLINE = 'online';

    public const COMING_SOON = 'coming_soon';

    public const MAINTENANCE = 'maintenance';

    public const SETTING_KEY = 'site_visibility_mode';

    /** @var array<string, int> */
    private const VALUES = [
        self::ONLINE => 0,
        self::COMING_SOON => 1,
        self::MAINTENANCE => 2,
    ];

    public function mode(): string
    {
        try {
            if (! Schema::hasTable('founder_settings')) {
                return $this->fallbackMode();
            }

            $value = (int) DB::table('founder_settings')
                ->where('key', self::SETTING_KEY)
                ->value('value');

            return array_search($value, self::VALUES, true) ?: self::ONLINE;
        } catch (Throwable) {
            return $this->fallbackMode();
        }
    }

    public function valueFor(string $mode): int
    {
        return self::VALUES[$mode] ?? self::VALUES[self::ONLINE];
    }

    /** @return list<string> */
    public function modes(): array
    {
        return array_keys(self::VALUES);
    }

    private function fallbackMode(): string
    {
        return app()->environment('production') ? self::MAINTENANCE : self::ONLINE;
    }
}
