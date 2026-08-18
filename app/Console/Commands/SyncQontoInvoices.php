<?php

namespace App\Console\Commands;

use App\Support\QontoInvoiceService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class SyncQontoInvoices extends Command
{
    protected $signature = 'qonto:sync-invoices {purchase? : UUID opzionale dell’acquisto}';

    protected $description = 'Aggiorna da Qonto lo stato delle fatture create e degli eventi di fatturazione elettronica';

    public function handle(QontoInvoiceService $invoices): int
    {
        $purchaseId = $this->argument('purchase');
        $query = DB::table('purchases')
            ->where('invoice_requested', true)
            ->whereNotNull('qonto_invoice_id');

        if ($purchaseId) {
            $query->where('id', (string) $purchaseId);
        }

        $purchaseIds = $query->pluck('id');

        if ($purchaseIds->isEmpty()) {
            $this->warn('Nessuna fattura Qonto da sincronizzare.');

            return self::SUCCESS;
        }

        $failed = 0;

        foreach ($purchaseIds as $id) {
            if ($invoices->syncForPurchase((string) $id)) {
                $this->line($id.': sincronizzata');
            } else {
                $failed++;
                $this->error($id.': sincronizzazione fallita');
            }
        }

        return $failed === 0 ? self::SUCCESS : self::FAILURE;
    }
}
