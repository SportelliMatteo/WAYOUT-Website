<?php

use Illuminate\Contracts\Console\Kernel;
use Illuminate\Foundation\Application;
use Symfony\Component\Console\Output\StreamOutput;

define('LARAVEL_START', microtime(true));
umask(0027);

$deployLogDirectory = __DIR__.'/storage/logs/deploy';

if (! is_dir($deployLogDirectory)) {
    @mkdir($deployLogDirectory, 0750, true);
}

$deployLogPath = is_dir($deployLogDirectory) && is_writable($deployLogDirectory)
    ? $deployLogDirectory.'/deploy.log'
    : __DIR__.'/deploy-error.log';

$writeDeployLog = static function (string $message) use ($deployLogPath): void {
    @file_put_contents(
        $deployLogPath,
        '['.gmdate('Y-m-d\TH:i:s\Z')."] {$message}\n",
        FILE_APPEND | LOCK_EX,
    );
};

$writeDeployLog('Avvio runner. PHP='.PHP_VERSION.' SAPI='.PHP_SAPI);

if (PHP_SAPI !== 'cli') {
    $writeDeployLog('Esecuzione rifiutata: il runner deve essere avviato come PHP CLI dal Cron.');
    http_response_code(404);
    exit(1);
}

$autoloadPath = __DIR__.'/vendor/autoload.php';

if (! is_file($autoloadPath)) {
    $writeDeployLog('File mancante: vendor/autoload.php. Il caricamento del pacchetto è incompleto.');
    exit(1);
}

try {
    require $autoloadPath;

    /** @var Application $app */
    $app = require_once __DIR__.'/bootstrap/app.php';
    $app->usePublicPath(dirname(__DIR__));

    /** @var Kernel $kernel */
    $kernel = $app->make(Kernel::class);
    $kernel->bootstrap();
} catch (Throwable $exception) {
    $writeDeployLog(sprintf(
        'Bootstrap fallito: %s: %s in %s:%d',
        $exception::class,
        $exception->getMessage(),
        basename($exception->getFile()),
        $exception->getLine(),
    ));
    exit(1);
}

$logHandle = @fopen($deployLogPath, 'ab');

if (! is_resource($logHandle)) {
    $writeDeployLog('Impossibile aprire il log di deploy in scrittura.');
    exit(1);
}

$output = new StreamOutput($logHandle);
$releaseId = trim((string) config('aruba.release_id'));
$releaseFile = __DIR__.'/RELEASE_ID';

if ($releaseId === '' && is_file($releaseFile)) {
    $releaseId = trim((string) file_get_contents($releaseFile));
}

// The production check runs in the same process and must see the resolved value.
config()->set('aruba.release_id', $releaseId);
$output->writeln('<info>Ambiente: '.app()->environment().'</info>');
$output->writeln('<info>Release: '.($releaseId ?: '[mancante]').'</info>');
$output->writeln('<info>Database: '.config('database.default').'</info>');
$output->writeln('<info>Storage scrivibile: '.(is_writable(storage_path()) ? 'sì' : 'no').'</info>');
$output->writeln('<info>Bootstrap cache scrivibile: '.(is_writable(base_path('bootstrap/cache')) ? 'sì' : 'no').'</info>');

if ($releaseId === '' || app()->environment() !== 'production') {
    $output->writeln('<error>Deploy rifiutato: APP_ENV=production e RELEASE_ID sono obbligatori.</error>');
    exit(1);
}

$deploymentDirectory = storage_path('app/private/deployments');

if (! is_dir($deploymentDirectory) && ! mkdir($deploymentDirectory, 0750, true) && ! is_dir($deploymentDirectory)) {
    $output->writeln('<error>Impossibile creare la directory dei deploy.</error>');
    exit(1);
}

$marker = $deploymentDirectory.'/'.hash('sha256', $releaseId).'.done';
$lockHandle = fopen($deploymentDirectory.'/deploy.lock', 'c+');

if (! is_resource($lockHandle) || ! flock($lockHandle, LOCK_EX | LOCK_NB)) {
    $output->writeln('<error>Un altro deploy è già in esecuzione.</error>');
    exit(1);
}

if (is_file($marker)) {
    $output->writeln('<info>Release già installata: '.$releaseId.'</info>');
    exit(0);
}

$commands = [
    ['migrate', ['--force' => true, '--no-interaction' => true]],
    ['optimize:clear', ['--no-interaction' => true]],
    ['config:cache', ['--no-interaction' => true]],
    ['route:cache', ['--no-interaction' => true]],
    ['view:cache', ['--no-interaction' => true]],
    ['app:production-check', ['--no-interaction' => true]],
];

foreach ($commands as [$command, $arguments]) {
    $output->writeln('<comment>Esecuzione: php artisan '.$command.'</comment>');

    try {
        $status = $kernel->call($command, $arguments, $output);
    } catch (Throwable $exception) {
        $output->writeln(sprintf(
            '<error>%s fallito: %s: %s in %s:%d</error>',
            $command,
            $exception::class,
            $exception->getMessage(),
            basename($exception->getFile()),
            $exception->getLine(),
        ));
        exit(1);
    }

    if ($status !== 0) {
        $output->writeln('<error>Deploy interrotto durante '.$command.'.</error>');
        exit($status);
    }
}

file_put_contents($marker, json_encode([
    'release_id' => $releaseId,
    'deployed_at_utc' => gmdate(DATE_ATOM),
], JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR), LOCK_EX);

$output->writeln('<info>Deploy completato: '.$releaseId.'</info>');
