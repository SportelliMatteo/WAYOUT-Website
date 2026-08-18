<?php

namespace App\Console\Commands;

use App\Support\QontoInvoiceService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class SendQontoInvoice extends Command
{
    protected $signature = 'qonto:send-invoice {purchase : UUID dell’acquisto}';

    protected $description = 'Invia o ritenta una fattura elettronica Qonto per un acquisto pagato';

    public function handle(QontoInvoiceService $invoices): int
    {
        $purchaseId = (string) $this->argument('purchase');
        $purchase = DB::table('purchases')->where('id', $purchaseId)->first();

        if (! $purchase) {
            $this->error('Acquisto non trovato.');

            return self::FAILURE;
        }

        if (! $purchase->invoice_requested || $purchase->status !== 'succeeded') {
            $this->error('L’acquisto deve essere pagato e avere una richiesta di fattura.');

            return self::FAILURE;
        }

        if ($invoices->sendForPurchase($purchaseId)) {
            $this->info('Fattura Qonto elaborata correttamente.');

            return self::SUCCESS;
        }

        $error = DB::table('purchases')->where('id', $purchaseId)->value('qonto_invoice_error');
        $this->error($error ?: 'Invio non eseguito. Verifica QONTO_INVOICING_ENABLED e la configurazione.');

        return self::FAILURE;
    }
}
