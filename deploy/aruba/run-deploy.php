<?php

use Illuminate\Contracts\Console\Kernel;
use Illuminate\Foundation\Application;
use Symfony\Component\Console\Output\ConsoleOutput;

define('LARAVEL_START', microtime(true));
umask(0027);

if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit(1);
}

require __DIR__.'/vendor/autoload.php';

/** @var Application $app */
$app = require_once __DIR__.'/bootstrap/app.php';
$app->usePublicPath(dirname(__DIR__));

/** @var Kernel $kernel */
$kernel = $app->make(Kernel::class);
$kernel->bootstrap();
$output = new ConsoleOutput;
$releaseId = trim((string) config('aruba.release_id'));

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
    $status = $kernel->call($command, $arguments, $output);

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
