<?php

namespace Tests\Feature;

use App\Mail\WithdrawalReceiptMail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class WithdrawalTest extends TestCase
{
    use RefreshDatabase;

    public function test_page_has_required_url_form_and_three_sections(): void
    {
        $this->get('/recedere-dal-contratto')
            ->assertOk()
            ->assertSee('Recesso e rimborsi')
            ->assertSee('Recedere dal contratto qui')
            ->assertSee('Informativa sul diritto di recesso')
            ->assertSee('Refund Policy')
            ->assertSee('Conferma recesso');

        $this->get('/recesso-e-rimborso')
            ->assertRedirect('/recedere-dal-contratto')
            ->assertStatus(301);
    }

    public function test_withdrawal_template_is_a_downloadable_editable_word_document(): void
    {
        $this->get(route('withdrawal.template.download'))
            ->assertOk()
            ->assertDownload('Modulo-tipo-di-recesso-WAYOUT.docx')
            ->assertHeader('content-type', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document');
    }

    public function test_review_does_not_register_until_final_confirmation(): void
    {
        $this->insertPurchase();

        $response = $this->post(route('withdrawal.review.store'), $this->validForm());

        $response->assertRedirect(route('withdrawal.review'));
        $this->assertDatabaseCount('withdrawal_requests', 0);

        $this->get(route('withdrawal.review'))
            ->assertOk()
            ->assertSee('Conferma recesso')
            ->assertSee('WO-2026-000123');
    }

    public function test_confirmation_records_before_sending_receipt_and_exposes_download(): void
    {
        Mail::fake();
        config()->set('email.enabled', true);

        $this->insertPurchase();

        $this->post(route('withdrawal.review.store'), $this->validForm());
        $review = session('withdrawal.review');

        $response = $this->post(route('withdrawal.confirm'), ['nonce' => $review['nonce']]);

        $withdrawal = DB::table('withdrawal_requests')->first();
        $this->assertNotNull($withdrawal);
        $this->assertSame(123, $withdrawal->purchase_id);
        $this->assertTrue((bool) $withdrawal->within_ordinary_period);
        $this->assertDatabaseHas('withdrawal_request_events', [
            'withdrawal_request_id' => $withdrawal->id,
            'event_type' => 'request.received',
        ]);
        Mail::assertSent(WithdrawalReceiptMail::class, 1);

        $response->assertRedirect(route('withdrawal.receipt', ['token' => $review['public_token']]));
        $this->get($response->headers->get('Location'))
            ->assertOk()
            ->assertSee($withdrawal->receipt_number)
            ->assertSee('Dichiarazione ricevuta');
        $this->get(route('withdrawal.receipt.download', ['token' => $review['public_token']]))
            ->assertOk()
            ->assertHeader('content-type', 'text/plain; charset=UTF-8')
            ->assertSee($withdrawal->receipt_number);
    }

    public function test_unknown_order_shows_popup_and_does_not_start_withdrawal(): void
    {
        $response = $this->from(route('legal.refunds'))->post(route('withdrawal.review.store'), $this->validForm([
            'order_reference' => 'CONTRATTO-ESTERNO-9',
        ]));

        $response->assertRedirect(route('legal.refunds'))
            ->assertSessionHas('order_not_found', true)
            ->assertSessionHasErrors([
                'order_reference' => "Ordine non esistente, controlla attentamente nell'email di acquisto",
            ]);

        $this->get(route('legal.refunds'))
            ->assertOk()
            ->assertSee('Ordine non trovato')
            ->assertSee("Ordine non esistente, controlla attentamente nell'email di acquisto", false)
            ->assertSee('value="CONTRATTO-ESTERNO-9"', false);

        $this->assertDatabaseCount('withdrawal_requests', 0);
        $this->assertNull(session('withdrawal.review'));
    }

    public function test_order_must_belong_to_purchase_email(): void
    {
        $this->insertPurchase();

        $this->post(route('withdrawal.review.store'), $this->validForm([
            'purchase_email' => 'altra@example.com',
        ]))->assertSessionHas('order_not_found', true);

        $this->assertNull(session('withdrawal.review'));
    }

    private function validForm(array $overrides = []): array
    {
        return array_merge([
            'first_name' => 'Ada',
            'last_name' => 'Lovelace',
            'purchase_email' => 'ada@example.com',
            'receipt_email' => 'ada@example.com',
            'order_reference' => 'WO-2026-000123',
            'purchase_date' => now()->subDays(3)->toDateString(),
            'plan' => 'join',
            'website' => '',
        ], $overrides);
    }

    private function insertPurchase(): void
    {
        DB::table('purchases')->insert([
            'id' => 123,
            'email' => 'ada@example.com',
            'plan' => 'join',
            'amount' => 2900,
            'currency' => 'eur',
            'status' => 'succeeded',
            'created_at' => now()->subDays(3),
            'updated_at' => now()->subDays(3),
        ]);
    }
}
