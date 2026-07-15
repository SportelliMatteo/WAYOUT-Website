<?php

return [
    'email' => env('ADMIN_EMAIL'),
    'password' => env('ADMIN_PASSWORD'),
    'max_users' => (int) env('ADMIN_MAX_USERS', 4),
    'otp_issuer' => env('ADMIN_OTP_ISSUER', 'WAYOUT Admin'),
];
