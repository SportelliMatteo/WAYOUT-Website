<?php

namespace App\Support;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;
use Throwable;

class QontoInvoiceService
{
    /** @return array{data: string, filename: string}|null */
    public function courtesyPdfForPurchase(string $purchaseId, int $maxAttempts = 6): ?array
    {
        if (! config('services.qonto.invoicing_enabled')) {
            return null;
        }

        $purchase = DB::table('purchases')->where('id', $purchaseId)->first();

        if (! $purchase?->invoice_requested || ! $purchase->qonto_invoice_id) {
            return null;
        }

        try {
            for ($attempt = 1; $attempt <= $maxAttempts; $attempt++) {
                $invoiceResponse = $this->request()->get('/v2/client_invoices/'.$purchase->qonto_invoice_id);

                if ($invoiceResponse->successful()) {
                    $attachmentId = $invoiceResponse->json('client_invoice.attachment_id');

                    if (is_string($attachmentId) && $attachmentId !== '') {
                        $attachmentResponse = $this->request()->get('/v2/attachments/'.$attachmentId);
                        $url = $attachmentResponse->successful()
                            ? $attachmentResponse->json('attachment.url')
                            : null;

                        if (is_string($url) && str_starts_with($url, 'https://')) {
                            $download = Http::timeout(20)->get($url);
                            $data = $download->successful() ? $download->body() : '';

                            if (str_starts_with($data, '%PDF-') && strlen($data) <= 15 * 1024 * 1024) {
                                $reference = $purchase->qonto_invoice_number ?: $purchase->order_reference;

                                return [
                                    'data' => $data,
                                    'filename' => 'fattura-cortesia-'.preg_replace('/[^A-Za-z0-9._-]/', '-', $reference).'.pdf',
                                ];
                            }
                        }
                    }
                }

                if ($attempt < $maxAttempts) {
                    usleep(2_000_000);
                }
            }
        } catch (Throwable $exception) {
            Log::warning('Qonto courtesy invoice PDF is not available yet.', [
                'purchase_id' => $purchaseId,
                'qonto_invoice_id' => $purchase->qonto_invoice_id,
                ...PrivacySafeLogContext::exception($exception),
            ]);
        }

        return null;
    }

    public function sendForPurchase(string $purchaseId): bool
    {
        if (! config('services.qonto.invoicing_enabled')) {
            return false;
        }

        $purchase = DB::table('purchases')->where('id', $purchaseId)->first();

        if (! $purchase || $purchase->status !== 'succeeded' || ! $purchase->invoice_requested) {
            return false;
        }

        if (in_array($purchase->electronic_invoice_status, ['sent', 'test_created'], true)) {
            return true;
        }

        if ($purchase->electronic_invoice_status === 'processing'
            && $purchase->qonto_invoice_attempted_at
            && now()->diffInMinutes($purchase->qonto_invoice_attempted_at) < 5) {
            return false;
        }

        $claimed = DB::table('purchases')
            ->where('id', $purchaseId)
            ->whereNotIn('electronic_invoice_status', ['sent', 'test_created', 'processing'])
            ->update([
                'electronic_invoice_status' => 'processing',
                'qonto_invoice_error' => null,
                'qonto_invoice_attempted_at' => now(),
                'updated_at' => now(),
            ]);

        if (! $claimed) {
            return false;
        }

        try {
            $this->assertConfigurationIsComplete();

            $clientId = $purchase->qonto_client_id ?: $this->findReusableClientId($purchase);

            if ($clientId) {
                $this->updateClient($purchase, $clientId);
            } else {
                $clientId = $this->createClient($purchase);
            }

            DB::table('purchases')->where('id', $purchaseId)->update([
                'qonto_client_id' => $clientId,
                'updated_at' => now(),
            ]);

            $invoice = $purchase->qonto_invoice_id
                ? ['id' => $purchase->qonto_invoice_id, 'number' => $purchase->qonto_invoice_number]
                : $this->createInvoice($purchase, $clientId);

            DB::table('purchases')->where('id', $purchaseId)->update([
                'qonto_invoice_id' => $invoice['id'],
                'qonto_invoice_number' => $invoice['number'],
                'updated_at' => now(),
            ]);

            $this->markInvoiceAsPaid($invoice['id']);

            $isProduction = config('services.qonto.environment') === 'production';

            DB::table('purchases')->where('id', $purchaseId)->update([
                'electronic_invoice_status' => $isProduction ? 'sent' : 'test_created',
                'qonto_invoice_id' => $invoice['id'],
                'qonto_invoice_number' => $invoice['number'],
                'qonto_invoice_status' => 'paid',
                'qonto_invoice_error' => null,
                'qonto_invoice_sent_at' => now(),
                'updated_at' => now(),
            ]);

            $this->syncForPurchase($purchaseId);

            return true;
        } catch (Throwable $exception) {
            DB::table('purchases')->where('id', $purchaseId)->update([
                'electronic_invoice_status' => 'failed',
                'qonto_invoice_error' => mb_substr($exception->getMessage(), 0, 4000),
                'updated_at' => now(),
            ]);

            Log::error('Qonto electronic invoice creation failed.', [
                'purchase_id' => $purchaseId,
                ...PrivacySafeLogContext::exception($exception),
            ]);

            return false;
        }
    }

    private function createClient(object $purchase): string
    {
        $response = $this->request()->post('/v2/clients', $this->clientPayload($purchase));
        $this->throwForFailedResponse($response->successful(), $response->status(), $response->body(), 'creazione cliente');

        $clientId = $response->json('client.id') ?? $response->json('data.id') ?? $response->json('id');

        if (! is_string($clientId) || $clientId === '') {
            throw new RuntimeException('Qonto non ha restituito l’identificativo del cliente.');
        }

        return $clientId;
    }

    private function updateClient(object $purchase, string $clientId): void
    {
        $response = $this->request()->patch('/v2/clients/'.$clientId, $this->clientPayload($purchase));
        $this->throwForFailedResponse($response->successful(), $response->status(), $response->body(), 'aggiornamento cliente');
    }

    /** @return array<string, mixed> */
    private function clientPayload(object $purchase): array
    {
        $isCompany = $purchase->billing_customer_type === 'legal_entity';
        $payload = [
            'kind' => $isCompany ? 'company' : 'individual',
            'email' => $purchase->email,
            'billing_address' => [
                'street_address' => $purchase->billing_address,
                'city' => $purchase->billing_city,
                'zip_code' => $purchase->billing_postal_code,
                'province_code' => $purchase->billing_province,
                'country_code' => strtoupper($purchase->billing_country),
            ],
            'currency' => strtoupper($purchase->currency),
            'locale' => 'IT',
        ];

        if ($isCompany) {
            $payload['name'] = $purchase->company_name;
            $payload['vat_number'] = ItalianFiscalData::qontoVatNumber(
                $purchase->vat_number,
                $purchase->billing_country,
            );
            $payload['tax_identification_number'] = ItalianFiscalData::normalizeVatNumber(
                $purchase->vat_number,
                $purchase->billing_country,
            );

            if ($purchase->sdi_code) {
                $payload['recipient_code'] = $purchase->sdi_code;
            }

            if ($purchase->pec && strcasecmp($purchase->pec, $purchase->email) !== 0) {
                $payload['extra_emails'] = [$purchase->pec];
            }
        } else {
            $payload['first_name'] = $purchase->first_name;
            $payload['last_name'] = $purchase->last_name;
            $payload['tax_identification_number'] = $purchase->fiscal_code;
        }

        return $payload;
    }

    public function syncForPurchase(string $purchaseId): bool
    {
        if (! config('services.qonto.invoicing_enabled')) {
            return false;
        }

        $purchase = DB::table('purchases')->where('id', $purchaseId)->first();

        if (! $purchase?->qonto_invoice_id) {
            return false;
        }

        try {
            $response = $this->request()->get('/v2/client_invoices/'.$purchase->qonto_invoice_id);
            $this->throwForFailedResponse($response->successful(), $response->status(), $response->body(), 'lettura stato fattura');

            $invoice = $response->json('client_invoice') ?? $response->json('data.attributes') ?? $response->json();
            $events = $invoice['einvoicing_lifecycle_events'] ?? [];

            DB::table('purchases')->where('id', $purchaseId)->update([
                'qonto_invoice_status' => $invoice['status'] ?? $purchase->qonto_invoice_status,
                'qonto_einvoicing_status' => $invoice['einvoicing_status'] ?? null,
                'qonto_einvoicing_events' => $events === [] ? null : json_encode($events, JSON_THROW_ON_ERROR),
                'qonto_invoice_error' => null,
                'qonto_invoice_synced_at' => now(),
                'updated_at' => now(),
            ]);

            return true;
        } catch (Throwable $exception) {
            DB::table('purchases')->where('id', $purchaseId)->update([
                'qonto_invoice_error' => mb_substr($exception->getMessage(), 0, 4000),
                'qonto_invoice_synced_at' => now(),
                'updated_at' => now(),
            ]);

            Log::warning('Qonto invoice status synchronization failed.', [
                'purchase_id' => $purchaseId,
                'qonto_invoice_id' => $purchase->qonto_invoice_id,
                ...PrivacySafeLogContext::exception($exception),
            ]);

            return false;
        }
    }

    private function findReusableClientId(object $purchase): ?string
    {
        return DB::table('purchases')
            ->where('id', '!=', $purchase->id)
            ->where('email', $purchase->email)
            ->where('billing_customer_type', $purchase->billing_customer_type)
            ->whereNotNull('qonto_client_id')
            ->when(
                $purchase->billing_customer_type === 'legal_entity',
                fn ($query) => $query->where('vat_number', $purchase->vat_number),
                fn ($query) => $query->where('fiscal_code', $purchase->fiscal_code),
            )
            ->latest('created_at')
            ->value('qonto_client_id');
    }

    /** @return array{id: string, number: ?string} */
    private function createInvoice(object $purchase, string $clientId): array
    {
        $vatRate = (float) config('services.qonto.vat_rate', 0.22);
        $grossAmount = ((int) $purchase->amount) / 100;
        $netAmount = $vatRate > 0 ? $grossAmount / (1 + $vatRate) : $grossAmount;
        $today = now()->toDateString();
        $isProduction = config('services.qonto.environment') === 'production';

        $response = $this->request()->post('/v2/client_invoices', [
            'client_id' => $clientId,
            'issue_date' => $today,
            'due_date' => $today,
            'currency' => strtoupper($purchase->currency),
            'payment_methods' => [
                'iban' => config('services.qonto.iban'),
            ],
            'items' => [[
                'title' => $purchase->plan === 'creator'
                    ? 'Founder 12M Creator Pass'
                    : 'Founder Join 12M Pass',
                'quantity' => '1',
                'unit_price' => [
                    'value' => number_format($netAmount, 2, '.', ''),
                    'currency' => strtoupper($purchase->currency),
                ],
                'vat_rate' => rtrim(rtrim(number_format($vatRate, 4, '.', ''), '0'), '.'),
                'description' => 'Ordine '.$purchase->order_reference,
            ]],
            'purchase_order' => mb_substr((string) $purchase->order_reference, 0, 40),
            'status' => 'unpaid',
            'report_einvoicing' => $isProduction,
        ]);
        $this->throwForFailedResponse($response->successful(), $response->status(), $response->body(), 'creazione fattura');

        $invoiceId = $response->json('client_invoice.id') ?? $response->json('data.id') ?? $response->json('id');
        $invoiceNumber = $response->json('client_invoice.number') ?? $response->json('data.attributes.number') ?? $response->json('number');

        if (! is_string($invoiceId) || $invoiceId === '') {
            throw new RuntimeException('Qonto non ha restituito l’identificativo della fattura.');
        }

        return [
            'id' => $invoiceId,
            'number' => is_string($invoiceNumber) && $invoiceNumber !== '' ? $invoiceNumber : null,
        ];
    }

    private function markInvoiceAsPaid(string $invoiceId): void
    {
        $response = $this->request()->post('/v2/client_invoices/'.$invoiceId.'/mark_as_paid', [
            'paid_at' => now()->toDateString(),
        ]);
        $this->throwForFailedResponse($response->successful(), $response->status(), $response->body(), 'registrazione pagamento');
    }

    private function request(): PendingRequest
    {
        $headers = [];
        $authMethod = config('services.qonto.auth_method', 'api_key');

        if ($authMethod === 'bearer') {
            $headers['Authorization'] = 'Bearer '.config('services.qonto.access_token');
        } else {
            $headers['Authorization'] = config('services.qonto.login').':'.config('services.qonto.secret_key');
        }

        if (config('services.qonto.environment') === 'sandbox') {
            $headers['X-Qonto-Staging-Token'] = config('services.qonto.staging_token');
        }

        return Http::baseUrl(config('services.qonto.base_url'))
            ->acceptJson()
            ->asJson()
            ->withHeaders($headers)
            ->timeout(15)
            ->retry(2, 250, throw: false);
    }

    private function assertConfigurationIsComplete(): void
    {
        $required = ['base_url', 'iban'];
        $required = config('services.qonto.auth_method') === 'bearer'
            ? [...$required, 'access_token']
            : [...$required, 'login', 'secret_key'];

        if (config('services.qonto.environment') === 'sandbox') {
            $required[] = 'staging_token';
        }

        foreach ($required as $key) {
            if (! filled(config('services.qonto.'.$key))) {
                throw new RuntimeException('Configurazione Qonto incompleta: manca '.$key.'.');
            }
        }
    }

    private function throwForFailedResponse(bool $successful, int $status, string $body, string $operation): void
    {
        if ($successful) {
            return;
        }

        throw new RuntimeException(sprintf(
            'Errore Qonto durante %s (HTTP %d): %s',
            $operation,
            $status,
            mb_substr($body, 0, 2000),
        ));
    }
}
