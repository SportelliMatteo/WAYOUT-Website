<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'key' => env('POSTMARK_API_KEY'),
    ],

    'resend' => [
        'key' => env('RESEND_API_KEY'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    'stripe' => [
        'key' => env('STRIPE_KEY'),
        'secret' => env('STRIPE_SECRET'),
        'webhook_secret' => env('STRIPE_WEBHOOK_SECRET'),
        // Hard-disabled at runtime; PHPUnit explicitly overrides this value.
        'direct_checkout_enabled_for_tests' => false,
    ],

    'qonto' => [
        'invoicing_enabled' => env('QONTO_INVOICING_ENABLED', false),
        'environment' => env('QONTO_ENVIRONMENT', 'sandbox'),
        'base_url' => env('QONTO_BASE_URL') ?: (
            env('QONTO_ENVIRONMENT', 'sandbox') === 'production'
                ? 'https://thirdparty.qonto.com'
                : 'https://thirdparty-sandbox.staging.qonto.co'
        ),
        'auth_method' => env('QONTO_AUTH_METHOD', 'api_key'),
        'login' => env('QONTO_LOGIN'),
        'secret_key' => env('QONTO_SECRET_KEY'),
        'access_token' => env('QONTO_ACCESS_TOKEN'),
        'staging_token' => env('QONTO_STAGING_TOKEN'),
        'iban' => env('QONTO_INVOICE_IBAN'),
        'vat_rate' => env('QONTO_INVOICE_VAT_RATE', 0.22),
    ],

    'firebase' => [
        'phone_verification_enabled' => env('FIREBASE_PHONE_VERIFICATION_ENABLED', false),
        'project_id' => env('FIREBASE_PROJECT_ID'),
        'client' => [
            'api_key' => env('FIREBASE_API_KEY'),
            'auth_domain' => env('FIREBASE_AUTH_DOMAIN'),
            'app_id' => env('FIREBASE_APP_ID'),
        ],
    ],

    'wayout_app' => [
        'base_url' => env('WAYOUT_APP_API_URL', 'https://staging-app.wayoutapp.it'),
        'connect_timeout' => env('WAYOUT_APP_API_CONNECT_TIMEOUT', 5),
        'timeout' => env('WAYOUT_APP_API_TIMEOUT', 15),
    ],

];
