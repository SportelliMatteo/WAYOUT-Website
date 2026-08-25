<?php

return [
    'headers_enabled' => env('SECURITY_HEADERS_ENABLED', true),
    'csp_enabled' => env('CONTENT_SECURITY_POLICY_ENABLED', env('APP_ENV') === 'production'),
    'hsts_enabled' => env('HSTS_ENABLED', env('APP_ENV') === 'production'),
    'hsts_max_age' => (int) env('HSTS_MAX_AGE', 31536000),

    'trusted_hosts' => array_values(array_map(
        static fn (string $host): string => '^'.preg_quote($host, '#').'$',
        array_filter(array_map('trim', explode(',', (string) env('TRUSTED_HOSTS', '')))),
    )),
];
