<?php

namespace Tests\Unit;

use Tests\TestCase;

class LoggingConfigurationTest extends TestCase
{
    public function test_application_and_email_logs_rotate_for_thirty_days(): void
    {
        $this->assertSame('daily', config('logging.channels.application.driver'));
        $this->assertSame(30, config('logging.channels.application.days'));
        $this->assertSame('daily', config('logging.channels.email.driver'));
        $this->assertSame(30, config('logging.channels.email.days'));
        $this->assertStringContainsString(
            'storage'.DIRECTORY_SEPARATOR.'logs'.DIRECTORY_SEPARATOR.'email',
            config('logging.channels.email.path'),
        );
    }

    public function test_schedule_errors_are_isolated_without_stack_traces(): void
    {
        $this->assertSame('daily', config('logging.channels.schedule.driver'));
        $this->assertSame('error', config('logging.channels.schedule.level'));
        $this->assertSame(30, config('logging.channels.schedule.days'));
        $this->assertFalse(config('logging.channels.schedule.formatter_with.includeStacktraces'));
        $this->assertStringContainsString(
            'storage'.DIRECTORY_SEPARATOR.'logs'.DIRECTORY_SEPARATOR.'schedule',
            config('logging.channels.schedule.path'),
        );
    }

    public function test_test_logs_are_isolated_and_rotate_for_seven_days(): void
    {
        $this->assertSame('testing', config('logging.default'));
        $this->assertSame('daily', config('logging.channels.testing.driver'));
        $this->assertSame(7, config('logging.channels.testing.days'));
        $this->assertStringContainsString(
            'storage'.DIRECTORY_SEPARATOR.'logs'.DIRECTORY_SEPARATOR.'testing',
            config('logging.channels.testing.path'),
        );
    }

    public function test_emergency_log_is_isolated_from_regular_logs(): void
    {
        $this->assertStringContainsString(
            'storage'.DIRECTORY_SEPARATOR.'logs'.DIRECTORY_SEPARATOR.'emergency',
            config('logging.channels.emergency.path'),
        );
        $this->assertStringNotContainsString('laravel.log', config('logging.channels.emergency.path'));
    }
}
