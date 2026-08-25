<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class BenefitEmailCodeMail extends Mailable
{
    use Queueable;
    use SerializesModels;

    public function __construct(public string $code, public int $expiresInMinutes) {}

    public function build(): static
    {
        return $this->subject(__('messages.email.benefit_code_subject'))
            ->replyTo(config('email.support_address'), config('email.support_name'))
            ->view('emails.benefit-email-code')
            ->text('emails.text.benefit-email-code');
    }
}
