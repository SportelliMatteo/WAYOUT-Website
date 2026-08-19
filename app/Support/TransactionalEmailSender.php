<?php

namespace App\Support;

use Illuminate\Mail\Mailable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Throwable;

class TransactionalEmailSender
{
    public function enabled(): bool
    {
        return (bool) config('email.enabled');
    }

    public function send(string $recipient, Mailable $mailable): bool
    {
        $logger = Log::channel(config('email.log_channel', 'email'));
        $context = [
            'recipient_hash' => PrivacySafeLogContext::fingerprint($recipient),
            'mailable' => $mailable::class,
            'mailer' => config('mail.default'),
            'smtp_host' => config('mail.mailers.smtp.host'),
            'smtp_port' => config('mail.mailers.smtp.port'),
            'username_configured' => filled(config('mail.mailers.smtp.username')),
            'password_configured' => filled(config('mail.mailers.smtp.password')),
            'from_configured' => filled(config('mail.from.address')),
        ];

        if (! $this->enabled()) {
            $logger->info('email.skipped', [
                ...$context,
                'reason' => 'EMAIL_SENDING_ENABLED is false',
            ]);

            return false;
        }

        $logger->info('email.attempt', $context);

        try {
            Mail::to($recipient)->send($mailable);
        } catch (Throwable $exception) {
            $logger->error('email.failed', [
                ...$context,
                ...PrivacySafeLogContext::exception($exception),
            ]);

            throw $exception;
        }

        $logger->info('email.sent', $context);

        return true;
    }
}
