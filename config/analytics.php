<?php

return [
    'enabled' => (bool) env('ANALYTICS_ENABLED', false),
    'consent_version' => max(1, (int) env('ANALYTICS_CONSENT_VERSION', 1)) + ((bool) env('META_CAPI_ENABLED', false) ? 1 : 0),
    'consent_days' => (int) env('ANALYTICS_CONSENT_DAYS', 180),
    'gtm_id' => env('ANALYTICS_GTM_ID'),
    'ga4_id' => env('ANALYTICS_GA4_ID'),
    'meta_pixel_id' => env('ANALYTICS_META_PIXEL_ID'),
    'meta_capi' => [
        'enabled' => (bool) env('META_CAPI_ENABLED', false),
        'access_token' => env('META_CAPI_ACCESS_TOKEN'),
        'api_version' => env('META_CAPI_API_VERSION', 'v23.0'),
        'test_event_code' => env('META_CAPI_TEST_EVENT_CODE'),
    ],
    'debug' => (bool) env('ANALYTICS_DEBUG', false),
];
