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

    'firebase' => [
        'project_id' => env('FIREBASE_PROJECT_ID'),
        'sms_resend_cooldown_seconds' => (int) env('FIREBASE_SMS_RESEND_COOLDOWN_SECONDS', 60),
        'client' => [
            'api_key' => env('FIREBASE_API_KEY'),
            'auth_domain' => env('FIREBASE_AUTH_DOMAIN'),
            'app_id' => env('FIREBASE_APP_ID'),
        ],
    ],

    'wayout' => [
        'base_url' => env('WAYOUT_BASE_URL', 'https://staging-app.wayoutapp.it'),
        'internal_secret' => env('WAYOUT_INTERNAL_SECRET'),
        'connect_timeout' => (int) env('WAYOUT_CONNECT_TIMEOUT', 5),
        'timeout' => (int) env('WAYOUT_TIMEOUT', 15),
        'catalog_cache_seconds' => (int) env('WAYOUT_CATALOG_CACHE_SECONDS', 60),
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
        'attachment_hosts' => env('QONTO_ATTACHMENT_HOSTS', 'qonto.com,amazonaws.com'),
        'iban' => env('QONTO_INVOICE_IBAN'),
        'vat_rate' => env('QONTO_INVOICE_VAT_RATE', 0.22),
    ],

];
