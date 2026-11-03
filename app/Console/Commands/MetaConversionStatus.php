<?php

namespace App\Console\Commands;

use App\Support\MetaConversions;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class MetaConversionStatus extends Command
{
    protected $signature = 'meta:status';

    protected $description = 'Mostra lo stato CAPI e i conteggi aggregati degli ultimi sette giorni senza inviare eventi';

    public function handle(MetaConversions $meta): int
    {
        $this->line('CAPI configurata: '.($meta->enabled() ? 'sì' : 'no'));
        $events = DB::table('meta_conversion_events')->where('created_at', '>=', now()->subDays(7));
        $rows = [];
        foreach (['lead_' => 'Lead', 'purchase_' => 'Purchase'] as $prefix => $name) {
            $query = (clone $events)->where('event_id', 'like', $prefix.'%');
            $rows[] = [
                $name,
                (clone $query)->count(),
                (clone $query)->whereNotNull('delivered_at')->count(),
                (clone $query)->whereNotNull('payload')->count(),
                (clone $query)->whereNotNull('payload')->where('attempts', '>', 0)->count(),
                (clone $query)->whereNull('payload')->whereNull('delivered_at')->count(),
            ];
        }
        $this->table(['Evento', 'Registrati', 'Consegnati', 'In coda', 'Da riprovare', 'Scartati'], $rows);
        $this->line('Ultima consegna: '.((clone $events)->max('delivered_at') ?: 'nessuna negli ultimi sette giorni'));
        $this->line('I conteggi del pixel e la copertura effettiva vanno confrontati in Gestione eventi Meta.');

        return self::SUCCESS;
    }
}
