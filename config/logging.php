<?php

use App\Logging\SetLogTimezone;
use Monolog\Formatter\LineFormatter;
use Monolog\Handler\NullHandler;
use Monolog\Handler\StreamHandler;
use Monolog\Handler\SyslogUdpHandler;
use Monolog\Processor\PsrLogMessageProcessor;

return [

    'timezone' => env('LOG_TIMEZONE', env('APP_DISPLAY_TIMEZONE', 'Europe/Rome')),

    /*
    |--------------------------------------------------------------------------
    | Default Log Channel
    |--------------------------------------------------------------------------
    |
    | This option defines the default log channel that is utilized to write
    | messages to your logs. The value provided here should match one of
    | the channels present in the list of "channels" configured below.
    |
    */

    'default' => env('LOG_CHANNEL', 'application'),

    /*
    |--------------------------------------------------------------------------
    | Deprecations Log Channel
    |--------------------------------------------------------------------------
    |
    | This option controls the log channel that should be used to log warnings
    | regarding deprecated PHP and library features. This allows you to get
    | your application ready for upcoming major versions of dependencies.
    |
    */

    'deprecations' => [
        'channel' => env('LOG_DEPRECATIONS_CHANNEL', 'null'),
        'trace' => env('LOG_DEPRECATIONS_TRACE', false),
    ],

    /*
    |--------------------------------------------------------------------------
    | Log Channels
    |--------------------------------------------------------------------------
    |
    | Here you may configure the log channels for your application. Laravel
    | utilizes the Monolog PHP logging library, which includes a variety
    | of powerful log handlers and formatters that you're free to use.
    |
    | Available drivers: "single", "daily", "slack", "syslog",
    |                    "errorlog", "monolog", "custom", "stack"
    |
    */

    'channels' => [

        'stack' => [
            'driver' => 'stack',
            'channels' => explode(',', (string) env('LOG_STACK', 'application')),
            'ignore_exceptions' => false,
        ],

        'application' => [
            'driver' => 'daily',
            'path' => storage_path('logs/application/application.log'),
            'level' => env('LOG_LEVEL', 'debug'),
            'days' => (int) env('LOG_DAILY_DAYS', 30),
            'tap' => [SetLogTimezone::class],
            'formatter' => LineFormatter::class,
            'formatter_with' => [
                'format' => "[%datetime%] %level_name% | %message% %context%\n",
                'dateFormat' => 'Y-m-d H:i:s',
                'allowInlineLineBreaks' => true,
                'ignoreEmptyContextAndExtra' => true,
                'includeStacktraces' => true,
            ],
            'replace_placeholders' => true,
        ],

        'testing' => [
            'driver' => 'daily',
            'path' => storage_path('logs/testing/testing.log'),
            'level' => env('TEST_LOG_LEVEL', 'debug'),
            'days' => (int) env('TEST_LOG_DAYS', 7),
            'tap' => [SetLogTimezone::class],
            'formatter' => LineFormatter::class,
            'formatter_with' => [
                'format' => "[%datetime%] %level_name% | %message% %context%\n",
                'dateFormat' => 'Y-m-d H:i:s',
                'allowInlineLineBreaks' => true,
                'ignoreEmptyContextAndExtra' => true,
                'includeStacktraces' => true,
            ],
            'replace_placeholders' => true,
        ],

        'single' => [
            // Compatibility alias for installations still using LOG_STACK=single.
            'driver' => 'daily',
            'path' => storage_path('logs/application/application.log'),
            'level' => env('LOG_LEVEL', 'debug'),
            'days' => (int) env('LOG_DAILY_DAYS', 30),
            'tap' => [SetLogTimezone::class],
            'formatter' => LineFormatter::class,
            'formatter_with' => [
                'format' => "[%datetime%] %level_name% | %message% %context%\n",
                'dateFormat' => 'Y-m-d H:i:s',
                'allowInlineLineBreaks' => true,
                'ignoreEmptyContextAndExtra' => true,
                'includeStacktraces' => true,
            ],
            'replace_placeholders' => true,
        ],

        'daily' => [
            'driver' => 'daily',
            'path' => storage_path('logs/application/application.log'),
            'level' => env('LOG_LEVEL', 'debug'),
            'days' => (int) env('LOG_DAILY_DAYS', 30),
            'tap' => [SetLogTimezone::class],
            'formatter' => LineFormatter::class,
            'formatter_with' => [
                'format' => "[%datetime%] %level_name% | %message% %context%\n",
                'dateFormat' => 'Y-m-d H:i:s',
                'allowInlineLineBreaks' => true,
                'ignoreEmptyContextAndExtra' => true,
                'includeStacktraces' => true,
            ],
            'replace_placeholders' => true,
        ],

        'email' => [
            'driver' => 'daily',
            'path' => storage_path('logs/email/email.log'),
            'level' => env('EMAIL_LOG_LEVEL', 'info'),
            'days' => (int) env('EMAIL_LOG_DAYS', 30),
            'tap' => [SetLogTimezone::class],
            'formatter' => LineFormatter::class,
            'formatter_with' => [
                'format' => "[%datetime%] %level_name% | %message% %context%\n",
                'dateFormat' => 'Y-m-d H:i:s',
                'allowInlineLineBreaks' => true,
                'ignoreEmptyContextAndExtra' => true,
                'includeStacktraces' => true,
            ],
            'replace_placeholders' => true,
        ],

        'schedule' => [
            'driver' => 'daily',
            'path' => storage_path('logs/schedule/schedule.log'),
            'level' => env('SCHEDULE_LOG_LEVEL', 'error'),
            'days' => (int) env('SCHEDULE_LOG_DAYS', 30),
            'tap' => [SetLogTimezone::class],
            'formatter' => LineFormatter::class,
            'formatter_with' => [
                'format' => "[%datetime%] %level_name% | %message% %context%\n",
                'dateFormat' => 'Y-m-d H:i:s',
                'allowInlineLineBreaks' => false,
                'ignoreEmptyContextAndExtra' => true,
                'includeStacktraces' => false,
            ],
            'replace_placeholders' => true,
        ],

        'slack' => [
            'driver' => 'slack',
            'url' => env('LOG_SLACK_WEBHOOK_URL'),
            'username' => env('LOG_SLACK_USERNAME', env('APP_NAME', 'Laravel')),
            'emoji' => env('LOG_SLACK_EMOJI', ':boom:'),
            'level' => env('LOG_LEVEL', 'critical'),
            'replace_placeholders' => true,
        ],

        'papertrail' => [
            'driver' => 'monolog',
            'level' => env('LOG_LEVEL', 'debug'),
            'handler' => env('LOG_PAPERTRAIL_HANDLER', SyslogUdpHandler::class),
            'handler_with' => [
                'host' => env('PAPERTRAIL_URL'),
                'port' => env('PAPERTRAIL_PORT'),
                'connectionString' => 'tls://'.env('PAPERTRAIL_URL').':'.env('PAPERTRAIL_PORT'),
            ],
            'processors' => [PsrLogMessageProcessor::class],
        ],

        'stderr' => [
            'driver' => 'monolog',
            'level' => env('LOG_LEVEL', 'debug'),
            'handler' => StreamHandler::class,
            'handler_with' => [
                'stream' => 'php://stderr',
            ],
            'formatter' => env('LOG_STDERR_FORMATTER'),
            'processors' => [PsrLogMessageProcessor::class],
        ],

        'syslog' => [
            'driver' => 'syslog',
            'level' => env('LOG_LEVEL', 'debug'),
            'facility' => env('LOG_SYSLOG_FACILITY', LOG_USER),
            'replace_placeholders' => true,
        ],

        'errorlog' => [
            'driver' => 'errorlog',
            'level' => env('LOG_LEVEL', 'debug'),
            'replace_placeholders' => true,
        ],

        'null' => [
            'driver' => 'monolog',
            'handler' => NullHandler::class,
        ],

        'emergency' => [
            'path' => storage_path('logs/emergency/emergency.log'),
        ],

    ],

];
