<?php

return [
    'magic_link_minutes' => (int) env('WAITLIST_MAGIC_LINK_MINUTES', 30),
    'verification_resend_seconds' => (int) env('WAITLIST_VERIFICATION_RESEND_SECONDS', 60),
    'email_challenge_minutes' => (int) env('BENEFIT_EMAIL_CHALLENGE_MINUTES', 10),
    'email_challenge_resend_seconds' => (int) env('BENEFIT_EMAIL_CHALLENGE_RESEND_SECONDS', 60),
    'email_challenge_max_attempts' => (int) env('BENEFIT_EMAIL_CHALLENGE_MAX_ATTEMPTS', 5),

    'server_auth' => [
        'key' => env('BENEFIT_API_KEY'),
        'secret' => env('BENEFIT_API_SECRET'),
        'max_clock_skew_seconds' => (int) env('BENEFIT_API_MAX_CLOCK_SKEW_SECONDS', 300),
    ],
];
