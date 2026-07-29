<?php

namespace App\Mail;

use App\Models\WithdrawalRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class WithdrawalReceiptMail extends Mailable
{
    use Queueable;
    use SerializesModels;

    public function __construct(
        public WithdrawalRequest $withdrawal,
        public string $publicToken,
    ) {}

    public function build(): static
    {
        return $this->subject('Ricevuta recesso '.$this->withdrawal->receipt_number)
            ->replyTo(config('email.support_address'), config('email.support_name'))
            ->view('emails.withdrawal-receipt')
            ->text('emails.text.withdrawal-receipt');
    }
}
