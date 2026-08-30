<?php

namespace Tests\Feature;

use App\Mail\ContactMessageMail;
use App\Mail\PurchaseConfirmationMail;
use App\Mail\WaitlistWelcomeMail;
use Tests\TestCase;

class EmailTemplateTest extends TestCase
{
    public function test_transactional_email_templates_render_with_legal_information(): void
    {
        $waitlist = (new WaitlistWelcomeMail([
            'email' => 'ada@example.com',
            'waitlist_position' => 42,
        ]))->render();

        $purchase = (new PurchaseConfirmationMail([
            'email' => 'ada@example.com',
            'plan_name' => 'Founder Join 12M Pass',
            'amount' => 2900,
            'currency' => 'eur',
        ]))->render();

        $contact = (new ContactMessageMail([
            'name' => 'Ada Lovelace',
            'email' => 'ada@example.com',
            'subject' => 'Informazioni',
            'message' => 'Vorrei maggiori informazioni.',
        ]))->render();

        foreach ([$waitlist, $purchase, $contact] as $html) {
            $this->assertStringContainsString('WAYOUT S.R.L.', $html);
            $this->assertStringContainsString(route('legal.privacy'), $html);
        }

        $this->assertStringNotContainsString('posizione nella waitlist', $waitlist);
        $this->assertStringNotContainsString('#42', $waitlist);
        $this->assertStringContainsString('29,00 EUR', $purchase);
        $this->assertStringContainsString(route('legal.presale'), $purchase);
        $this->assertStringContainsString('ada@example.com', $contact);
    }

    public function test_purchase_confirmation_can_attach_a_courtesy_invoice_pdf(): void
    {
        $mail = new PurchaseConfirmationMail([
            'email' => 'ada@example.com',
            'plan_name' => 'Founder Join 12M Pass',
            'amount' => 2900,
            'currency' => 'eur',
            'invoice_requested' => true,
            'invoice_status' => 'sent',
            'attachment_kind' => 'courtesy_invoice',
        ], [
            'data' => '%PDF-1.7 test',
            'filename' => 'fattura-cortesia-1.pdf',
        ]);

        $mail->assertHasAttachedData('%PDF-1.7 test', 'fattura-cortesia-1.pdf', [
            'mime' => 'application/pdf',
        ]);
        $this->assertStringContainsString('copia PDF di cortesia', $mail->render());
        $this->assertStringContainsString('formato XML', $mail->render());
    }

    public function test_failed_invoice_email_does_not_claim_that_processing_is_in_progress(): void
    {
        $html = (new PurchaseConfirmationMail([
            'email' => 'ada@example.com',
            'plan_name' => 'Founder Join 12M Pass',
            'amount' => 2900,
            'currency' => 'eur',
            'invoice_requested' => true,
            'invoice_status' => 'failed',
        ]))->render();

        $this->assertStringContainsString('Qonto non ha accettato', $html);
        $this->assertStringNotContainsString('elaborazione elettronica è in corso', $html);
    }

    public function test_purchase_confirmation_can_render_in_english(): void
    {
        $mail = (new PurchaseConfirmationMail([
            'order_reference' => 'WYO-ENGLISH',
            'email' => 'ada@example.com',
            'plan_name' => 'Founder Join 12M Pass',
            'amount' => 2900,
            'currency' => 'eur',
            'attachment_kind' => 'order_summary',
        ]))->locale('en');

        $html = $mail->render();

        $this->assertStringContainsString('Founder Pass purchase confirmation', $html);
        $this->assertStringContainsString('Order number', $html);
        $this->assertStringContainsString('?lang=en', $html);
        $this->assertStringNotContainsString('Numero d’ordine', $html);
    }
}
