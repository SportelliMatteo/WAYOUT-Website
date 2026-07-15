<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class PurchaseConfirmationMail extends Mailable
{
    use Queueable;
    use SerializesModels;

    /**
     * @param  array{email: string, plan_name: string, amount: int, currency: string}  $purchase
     */
    public function __construct(public array $purchase) {}

    public function build(): static
    {
        return $this->subject(__('messages.email.purchase_subject'))
            ->replyTo(config('email.support_address'), config('email.support_name'))
            ->view('emails.purchase-confirmation')
            ->text('emails.text.purchase-confirmation');
    }
}
