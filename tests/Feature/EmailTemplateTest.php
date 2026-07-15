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
            'first_name' => 'Ada',
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

        $this->assertStringContainsString('29,00 EUR', $purchase);
        $this->assertStringContainsString('ada@example.com', $contact);
    }
}
