<?php

namespace Tests\Unit;

use App\Support\ArubaScheduleRunner;
use DateTimeImmutable;
use DateTimeZone;
use Tests\TestCase;

class ArubaScheduleRunnerTest extends TestCase
{
    public function test_qonto_sync_is_due_on_every_cron_invocation(): void
    {
        $commands = app(ArubaScheduleRunner::class)->commandsDueAt(
            new DateTimeImmutable('2026-08-31 08:10:00', new DateTimeZone('UTC')),
        );

        $this->assertSame(['qonto:sync-invoices'], array_column($commands, 'command'));
    }

    public function test_retention_is_due_at_minute_twenty_in_the_application_timezone(): void
    {
        config()->set('app.display_timezone', 'Europe/Rome');

        $commands = app(ArubaScheduleRunner::class)->commandsDueAt(
            new DateTimeImmutable('2026-08-31 08:20:00', new DateTimeZone('UTC')),
        );

        $this->assertSame(
            ['qonto:sync-invoices', 'privacy:enforce-retention'],
            array_column($commands, 'command'),
        );
        $this->assertSame('scheduled', $commands[1]['arguments']['--trigger']);
    }
}
