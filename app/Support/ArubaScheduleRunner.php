<?php

namespace App\Support;

use DateTimeImmutable;
use DateTimeZone;

final class ArubaScheduleRunner
{
    /**
     * @return list<array{command: string, arguments: array<string, bool|string>}>
     */
    public function commandsDueAt(DateTimeImmutable $now): array
    {
        $timezone = new DateTimeZone((string) config('app.display_timezone', 'Europe/Rome'));
        $localNow = $now->setTimezone($timezone);
        $commands = [[
            'command' => 'qonto:sync-invoices',
            'arguments' => ['--no-interaction' => true],
        ]];

        if ((int) $localNow->format('i') === DataRetentionService::SCHEDULE_MINUTE) {
            $commands[] = [
                'command' => 'privacy:enforce-retention',
                'arguments' => [
                    '--trigger' => 'scheduled',
                    '--no-interaction' => true,
                ],
            ];
        }

        return $commands;
    }
}
