<?php

namespace App\Support;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

final class AppStoreLinks
{
    public function all(): array
    {
        $saved = Schema::hasTable('site_settings')
            ? DB::table('site_settings')->whereIn('key', ['app_store_apple', 'app_store_google'])->pluck('value', 'key')->all()
            : [];
        $links = [];
        foreach (['apple', 'google'] as $store) {
            $key = 'app_store_'.$store;
            $value = array_key_exists($key, $saved) ? $saved[$key] : config('services.app_stores.'.$store);
            $links[$store] = is_string($value) && filter_var($value, FILTER_VALIDATE_URL) && strtolower((string) parse_url($value, PHP_URL_SCHEME)) === 'https' ? $value : null;
        }

        return $links;
    }

    public function save(array $links): void
    {
        $rows = [];
        foreach (['apple', 'google'] as $store) {
            $rows[] = ['id' => DatabaseUuid::new(), 'key' => 'app_store_'.$store,
                'value' => $links[$store] ?? null, 'created_at' => now(), 'updated_at' => now()];
        }
        DB::table('site_settings')->upsert($rows, ['key'], ['value', 'updated_at']);
    }
}
