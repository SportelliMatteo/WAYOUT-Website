<?php

use App\Support\ArubaScheduleRunner;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Log;
use Symfony\Component\Console\Output\BufferedOutput;

define('LARAVEL_START', microtime(true));
umask(0027);

if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit(1);
}

$scheduleLogDirectory = __DIR__.'/storage/logs/schedule';

if (! is_dir($scheduleLogDirectory)) {
    @mkdir($scheduleLogDirectory, 0750, true);
}

$scheduleTimezone = new DateTimeZone('Europe/Rome');
$scheduleLogPath = $scheduleLogDirectory.'/schedule-'.(new DateTimeImmutable('now', $scheduleTimezone))->format('Y-m-d').'.log';
$writeError = static function (string $message, array $context = []) use ($scheduleLogPath): void {
    $timestamp = new DateTimeImmutable('now', new DateTimeZone('Europe/Rome'));
    $line = '['.$timestamp->format('Y-m-d H:i:s').'] ERROR | '.$message;

    if ($context !== []) {
        $encoded = json_encode($context, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        $line .= ' '.(is_string($encoded) ? $encoded : '{"context":"unavailable"}');
    }

    @file_put_contents($scheduleLogPath, $line."\n", FILE_APPEND | LOCK_EX);
};

$lockDirectory = __DIR__.'/storage/app/private';

if (! is_dir($lockDirectory)) {
    @mkdir($lockDirectory, 0750, true);
}

$lock = @fopen($lockDirectory.'/schedule.lock', 'c+');

if (! is_resource($lock)) {
    $writeError('Impossibile creare il lock del Cron.');
    exit(1);
}

if (! flock($lock, LOCK_EX | LOCK_NB)) {
    fclose($lock);
    exit(0);
}

try {
    require __DIR__.'/vendor/autoload.php';

    /** @var Application $app */
    $app = require_once __DIR__.'/bootstrap/app.php';
    $app->usePublicPath(dirname(__DIR__));

    /** @var Kernel $kernel */
    $kernel = $app->make(Kernel::class);
    $kernel->bootstrap();

    config()->set('logging.default', 'schedule');
    config()->set('logging.deprecations.channel', 'schedule');
    Log::setDefaultDriver('schedule');

    $runner = $app->make(ArubaScheduleRunner::class);
    $status = 0;

    foreach ($runner->commandsDueAt(new DateTimeImmutable('now', new DateTimeZone('UTC'))) as $scheduled) {
        $output = new BufferedOutput;
        $commandStatus = $kernel->call($scheduled['command'], $scheduled['arguments'], $output);

        if ($commandStatus !== 0) {
            $writeError('Comando pianificato fallito.', [
                'command' => $scheduled['command'],
                'exit_status' => $commandStatus,
            ]);
            $status = $commandStatus;
        }
    }
} catch (Throwable $exception) {
    $writeError('Esecuzione del Cron fallita.', [
        'exception_class' => $exception::class,
        'error_code' => $exception->getCode(),
        'message' => mb_substr($exception->getMessage(), 0, 300),
    ]);
    $status = 1;
} finally {
    flock($lock, LOCK_UN);
    fclose($lock);
}

exit($status);
