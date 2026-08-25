<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class WaitlistWelcomeMail extends Mailable
{
    use Queueable;
    use SerializesModels;

    /** @param array{email: string, waitlist_position: int, marketing_revocation_url?: string|null} $member */
    public function __construct(public array $member) {}

    public function build(): static
    {
        return $this->subject(__('messages.email.waitlist_subject'))
            ->replyTo(config('email.support_address'), config('email.support_name'))
            ->view('emails.waitlist-welcome')
            ->text('emails.text.waitlist-welcome');
    }
}
