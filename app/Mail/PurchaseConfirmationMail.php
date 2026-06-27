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
    public function __construct(public array $purchase)
    {
    }

    public function build(): static
    {
        return $this->subject('Conferma acquisto Founder Pass WAYOUT')
            ->view('emails.purchase-confirmation');
    }
}
