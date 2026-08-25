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
$status = $kernel->call('schedule:run', ['--no-interaction' => true], new ConsoleOutput);

exit($status);
