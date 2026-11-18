<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WithdrawalTest extends TestCase
{
    use RefreshDatabase;

    public function test_page_shows_backend_legal_documents_and_a_direct_support_form(): void
    {
        $this->get('/recedere-dal-contratto')->assertOk()
            ->assertSee('Recesso e rimborsi')->assertSee('Informativa sul diritto di recesso')->assertSee('Refund Policy')
            ->assertSee('Invia richiesta di recesso e rimborso')->assertSee('name="message"', false)
            ->assertDontSee('name="purchase_date"', false)->assertDontSee('name="plan"', false);
        $this->get('/recesso-e-rimborso')->assertRedirect('/recedere-dal-contratto')->assertStatus(301);
    }

    public function test_old_confirmation_urls_return_to_the_direct_ticket_form(): void
    {
        $this->get(route('withdrawal.review'))->assertRedirect(route('legal.refunds'));
        $this->post(route('withdrawal.confirm'), ['nonce' => 'old'])->assertRedirect(route('legal.refunds'));
        $this->assertDatabaseCount('withdrawal_requests', 0);
    }

    public function test_template_remains_downloadable_in_italian_even_with_an_english_link(): void
    {
        $this->get(route('withdrawal.template.download', ['lang' => 'en']))->assertOk()
            ->assertDownload('Modulo-tipo-di-recesso-WAYOUT.docx')
            ->assertHeader('content-type', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document');
    }
}
