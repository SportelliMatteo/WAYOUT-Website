<?php

namespace Tests\Feature;

use App\Support\DatabaseUuid;
use App\Support\QontoInvoiceService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class QontoInvoiceServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_sandbox_creates_an_individual_test_invoice_without_sending_it_to_sdi(): void
    {
        $this->configureQonto('sandbox');
        $purchaseId = $this->createPurchase([
            'billing_customer_type' => 'individual',
            'fiscal_code' => 'RSSMRA85T10A562S',
        ]);

        $this->fakeQonto();

        $this->assertTrue(app(QontoInvoiceService::class)->sendForPurchase($purchaseId));

        $this->assertDatabaseHas('purchases', [
            'id' => $purchaseId,
            'electronic_invoice_status' => 'test_created',
            'qonto_client_id' => '10000000-0000-4000-8000-000000000001',
            'qonto_invoice_id' => '20000000-0000-4000-8000-000000000002',
            'qonto_invoice_number' => 'TEST-1',
            'qonto_invoice_status' => 'paid',
            'qonto_einvoicing_status' => 'not_applicable',
        ]);

        Http::assertSent(function (Request $request) {
            if (! str_ends_with($request->url(), '/v2/clients')) {
                return false;
            }

            return $request->header('Authorization')[0] === 'sandbox-login:sandbox-secret'
                && $request->header('X-Qonto-Staging-Token')[0] === 'staging-token'
                && $request['kind'] === 'individual'
                && $request['tax_identification_number'] === 'RSSMRA85T10A562S';
        });

        Http::assertSent(function (Request $request) {
            if (! str_ends_with($request->url(), '/v2/client_invoices')) {
                return false;
            }

            return $request['report_einvoicing'] === false
                && $request['items'][0]['unit_price']['value'] === '23.77'
                && $request['items'][0]['vat_rate'] === '0.22';
        });
    }

    public function test_production_creates_a_company_invoice_for_automatic_sdi_reporting(): void
    {
        $this->configureQonto('production');
        $purchaseId = $this->createPurchase([
            'billing_customer_type' => 'legal_entity',
            'fiscal_code' => null,
            'company_name' => 'Wayout Test S.r.l.',
            'vat_number' => '14805930964',
            'sdi_code' => 'ABC1234',
            'pec' => 'amministrazione@pec.example.it',
        ]);

        $this->fakeQonto();

        $this->assertTrue(app(QontoInvoiceService::class)->sendForPurchase($purchaseId));
        $this->assertDatabaseHas('purchases', [
            'id' => $purchaseId,
            'electronic_invoice_status' => 'sent',
        ]);

        Http::assertSent(function (Request $request) {
            if (! str_ends_with($request->url(), '/v2/clients')) {
                return false;
            }

            return $request->header('X-Qonto-Staging-Token') === []
                && $request['kind'] === 'company'
                && $request['name'] === 'Wayout Test S.r.l.'
                && $request['vat_number'] === 'IT14805930964'
                && $request['tax_identification_number'] === '14805930964'
                && $request['recipient_code'] === 'ABC1234'
                && $request['extra_emails'] === ['amministrazione@pec.example.it'];
        });

        Http::assertSent(fn (Request $request) => str_ends_with($request->url(), '/v2/client_invoices')
            && $request['report_einvoicing'] === true);
    }

    public function test_retry_reuses_an_invoice_created_before_marking_it_as_paid_failed(): void
    {
        $this->configureQonto('sandbox');
        $purchaseId = $this->createPurchase([
            'fiscal_code' => 'RSSMRA85T10A562S',
        ]);
        $markAttempts = 0;

        Http::fake(function (Request $request) use (&$markAttempts) {
            if (str_ends_with($request->url(), '/v2/clients') || str_contains($request->url(), '/v2/clients/')) {
                return Http::response(['client' => ['id' => '10000000-0000-4000-8000-000000000001']], 201);
            }

            if (str_ends_with($request->url(), '/v2/client_invoices')) {
                return Http::response(['client_invoice' => [
                    'id' => '20000000-0000-4000-8000-000000000002',
                    'number' => 'TEST-1',
                ]], 201);
            }

            if ($request->method() === 'GET' && str_contains($request->url(), '/v2/client_invoices/')) {
                return Http::response(['client_invoice' => [
                    'status' => 'paid',
                    'einvoicing_status' => 'not_applicable',
                    'einvoicing_lifecycle_events' => [],
                ]]);
            }

            $markAttempts++;

            return $markAttempts <= 3
                ? Http::response(['error' => 'temporary'], 500)
                : Http::response(['client_invoice' => ['status' => 'paid']], 200);
        });

        $this->assertFalse(app(QontoInvoiceService::class)->sendForPurchase($purchaseId));
        $this->assertDatabaseHas('purchases', [
            'id' => $purchaseId,
            'electronic_invoice_status' => 'failed',
            'qonto_invoice_id' => '20000000-0000-4000-8000-000000000002',
        ]);

        $this->assertTrue(app(QontoInvoiceService::class)->sendForPurchase($purchaseId));
        $invoiceCreations = collect(Http::recorded())
            ->filter(fn (array $pair) => str_ends_with($pair[0]->url(), '/v2/client_invoices'));
        $this->assertCount(1, $invoiceCreations);
    }

    public function test_it_downloads_the_qonto_courtesy_pdf_attachment(): void
    {
        $this->configureQonto('sandbox');
        $purchaseId = $this->createPurchase([
            'fiscal_code' => 'RSSMRA85T10A562S',
            'electronic_invoice_status' => 'test_created',
            'qonto_invoice_id' => '20000000-0000-4000-8000-000000000002',
            'qonto_invoice_number' => 'TEST/2026/1',
        ]);

        Http::fake([
            'https://thirdparty-sandbox.staging.qonto.co/v2/client_invoices/*' => Http::response([
                'client_invoice' => ['attachment_id' => '30000000-0000-4000-8000-000000000003'],
            ]),
            'https://thirdparty-sandbox.staging.qonto.co/v2/attachments/*' => Http::response([
                'attachment' => ['url' => 'https://files.example.test/invoice.pdf'],
            ]),
            'https://files.example.test/invoice.pdf' => Http::response('%PDF-1.7 courtesy invoice', 200, [
                'Content-Type' => 'application/pdf',
            ]),
        ]);

        $attachment = app(QontoInvoiceService::class)->courtesyPdfForPurchase($purchaseId, 1);

        $this->assertSame('%PDF-1.7 courtesy invoice', $attachment['data']);
        $this->assertSame('fattura-cortesia-TEST-2026-1.pdf', $attachment['filename']);
    }

    private function configureQonto(string $environment): void
    {
        config()->set('services.qonto', [
            'invoicing_enabled' => true,
            'environment' => $environment,
            'base_url' => $environment === 'production'
                ? 'https://thirdparty.qonto.com'
                : 'https://thirdparty-sandbox.staging.qonto.co',
            'auth_method' => 'api_key',
            'login' => 'sandbox-login',
            'secret_key' => 'sandbox-secret',
            'access_token' => null,
            'staging_token' => $environment === 'sandbox' ? 'staging-token' : null,
            'iban' => 'IT60X0542811101000000123456',
            'vat_rate' => 0.22,
        ]);
    }

    private function fakeQonto(): void
    {
        Http::fake(function (Request $request) {
            if (str_ends_with($request->url(), '/v2/clients') || str_contains($request->url(), '/v2/clients/')) {
                return Http::response(['client' => ['id' => '10000000-0000-4000-8000-000000000001']], 201);
            }

            if (str_ends_with($request->url(), '/v2/client_invoices')) {
                return Http::response(['client_invoice' => [
                    'id' => '20000000-0000-4000-8000-000000000002',
                    'number' => 'TEST-1',
                ]], 201);
            }

            if (str_ends_with($request->url(), '/mark_as_paid')) {
                return Http::response(['client_invoice' => ['status' => 'paid']], 200);
            }

            if ($request->method() === 'GET' && str_contains($request->url(), '/v2/client_invoices/')) {
                return Http::response(['client_invoice' => [
                    'status' => 'paid',
                    'einvoicing_status' => 'not_applicable',
                    'einvoicing_lifecycle_events' => [[
                        'status' => 'created',
                        'occurred_at' => '2026-08-18T10:00:00Z',
                    ]],
                ]]);
            }

            return Http::response([], 404);
        });
    }

    /** @param array<string, mixed> $overrides */
    private function createPurchase(array $overrides): string
    {
        $id = DatabaseUuid::new();

        DB::table('purchases')->insert([
            'id' => $id,
            'order_reference' => 'WO-2026-'.$id,
            'email' => 'invoice@example.com',
            'first_name' => 'Ada',
            'last_name' => 'Lovelace',
            'birth_date' => '1990-01-01',
            'plan' => 'join',
            'amount' => 2900,
            'currency' => 'eur',
            'stripe_session_id' => 'direct_test',
            'status' => 'succeeded',
            'invoice_requested' => true,
            'fiscal_code' => null,
            'billing_customer_type' => 'individual',
            'billing_address' => 'Via Roma 1',
            'billing_postal_code' => '20100',
            'billing_city' => 'Milano',
            'billing_province' => 'MI',
            'billing_country' => 'IT',
            'company_name' => null,
            'vat_number' => null,
            'sdi_code' => null,
            'pec' => null,
            'electronic_invoice_status' => 'pending',
            'created_at' => now(),
            'updated_at' => now(),
            ...$overrides,
        ]);

        return $id;
    }
}
