<?php

namespace App\Console\Commands;

use App\Support\DataRetentionService;
use Illuminate\Console\Command;

class EnforceDataRetention extends Command
{
    protected $signature = 'privacy:enforce-retention
        {--dry-run : Conta i record senza cancellarli}
        {--trigger=manual : Origine dell’esecuzione: manual o scheduled}';

    protected $description = 'Applica le regole automatiche di conservazione dei dati personali';

    public function handle(DataRetentionService $retention): int
    {
        $trigger = (string) $this->option('trigger');

        if (! in_array($trigger, ['manual', 'scheduled'], true)) {
            $this->error('Il trigger deve essere manual oppure scheduled.');

            return self::INVALID;
        }

        $run = $retention->run((bool) $this->option('dry-run'), $trigger);
        $results = json_decode((string) $run->results, true) ?: [];

        $this->table(
            ['Regola', 'Trovati', 'Eliminati', 'Data limite'],
            collect($results)->map(fn (array $result, string $rule): array => [
                $rule,
                $result['matched'],
                $result['affected'],
                $result['cutoff'],
            ])->values()->all(),
        );

        if ($run->status !== 'completed') {
            $this->error((string) $run->error_message);

            return self::FAILURE;
        }

        $this->info($run->dry_run ? 'Simulazione completata.' : 'Retention completata.');

        return self::SUCCESS;
    }
}
