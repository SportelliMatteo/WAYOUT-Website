<?php

return [
    'magic_link_minutes' => (int) env('WAITLIST_MAGIC_LINK_MINUTES', 30),
    'verification_resend_seconds' => (int) env('WAITLIST_VERIFICATION_RESEND_SECONDS', 60),
    'api_default_per_page' => (int) env('BENEFIT_API_DEFAULT_PER_PAGE', 100),
    'api_max_per_page' => (int) env('BENEFIT_API_MAX_PER_PAGE', 200),

    'server_auth' => [
        'key' => env('BENEFIT_API_KEY'),
        'secret' => env('BENEFIT_API_SECRET'),
        'max_clock_skew_seconds' => (int) env('BENEFIT_API_MAX_CLOCK_SKEW_SECONDS', 300),
    ],
];
