<?php

return [
    'enabled' => env('EMAIL_SENDING_ENABLED', false),
    'contact_recipient' => env('CONTACT_EMAIL', env('ADMIN_EMAIL')),
    'support_address' => env('MAIL_REPLY_TO_ADDRESS', 'amministrazione@wayoutapp.it'),
    'support_name' => env('MAIL_REPLY_TO_NAME', 'WAYOUT'),
    'resend_cooldown_seconds' => (int) env('EMAIL_RESEND_COOLDOWN_SECONDS', 300),
    'log_channel' => env('EMAIL_LOG_CHANNEL', 'email'),
];
