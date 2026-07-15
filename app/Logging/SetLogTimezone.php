<?php

namespace App\Logging;

use DateTimeZone;
use Illuminate\Log\Logger;

class SetLogTimezone
{
    public function __invoke(Logger $logger): void
    {
        $monolog = $logger->getLogger();

        if (method_exists($monolog, 'setTimezone')) {
            $monolog->setTimezone(new DateTimeZone(
                config('logging.timezone', 'Europe/Rome')
            ));
        }
    }
}
