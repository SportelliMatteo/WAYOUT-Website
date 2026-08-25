<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class WaitlistVerificationMail extends Mailable
{
    use Queueable;
    use SerializesModels;

    public function __construct(public string $verificationUrl) {}

    public function build(): static
    {
        return $this->subject(__('messages.email.waitlist_verification_subject'))
            ->replyTo(config('email.support_address'), config('email.support_name'))
            ->view('emails.waitlist-verification')
            ->text('emails.text.waitlist-verification');
    }
}
