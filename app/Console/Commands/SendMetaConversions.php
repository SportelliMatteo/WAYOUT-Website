<?php

namespace App\Console\Commands;

use App\Support\MetaConversions;
use Illuminate\Console\Command;

class SendMetaConversions extends Command
{
    protected $signature = 'meta:send-conversions';

    protected $description = 'Invia a Meta le conversioni confermate con consenso Marketing valido';

    public function handle(MetaConversions $meta): int
    {
        $failed = $meta->sendPending();
        $this->line('Invio conversioni completato. Invii da riprovare: '.$failed);

        return $failed ? self::FAILURE : self::SUCCESS;
    }
}
