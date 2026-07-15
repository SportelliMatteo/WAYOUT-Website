<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ContactMessageMail extends Mailable
{
    use Queueable;
    use SerializesModels;

    /** @param array{name: string, email: string, subject: string, message: string} $contact */
    public function __construct(public array $contact) {}

    public function build(): static
    {
        return $this->subject(__('messages.email.contact_subject', ['subject' => $this->contact['subject']]))
            ->replyTo($this->contact['email'], $this->contact['name'])
            ->view('emails.contact-message')
            ->text('emails.text.contact-message');
    }
}
