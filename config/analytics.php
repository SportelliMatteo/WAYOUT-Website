<?php

return [
    'enabled' => (bool) env('ANALYTICS_ENABLED', false),
    'consent_version' => (int) env('ANALYTICS_CONSENT_VERSION', 1),
    'consent_days' => (int) env('ANALYTICS_CONSENT_DAYS', 180),
    'gtm_id' => env('ANALYTICS_GTM_ID'),
    'ga4_id' => env('ANALYTICS_GA4_ID'),
    'meta_pixel_id' => env('ANALYTICS_META_PIXEL_ID'),
    'debug' => (bool) env('ANALYTICS_DEBUG', false),
];
