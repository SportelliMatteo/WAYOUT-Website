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
     * @param  array{order_reference?: string, email: string, plan_name: string, amount: int, currency: string, invoice_requested?: bool, invoice_status?: string, attachment_kind?: string}  $purchase
     * @param  array{data: string, filename: string}|null  $pdfAttachment
     */
    public function __construct(public array $purchase, public ?array $pdfAttachment = null) {}

    public function build(): static
    {
        $mail = $this->subject(__('messages.email.purchase_subject'))
            ->replyTo(config('email.support_address'), config('email.support_name'))
            ->view('emails.purchase-confirmation')
            ->text('emails.text.purchase-confirmation');

        if ($this->pdfAttachment) {
            $mail->attachData(
                $this->pdfAttachment['data'],
                $this->pdfAttachment['filename'],
                ['mime' => 'application/pdf'],
            );
        }

        return $mail;
    }
}
