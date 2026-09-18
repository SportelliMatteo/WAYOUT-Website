<?php

// Upload this file directly into _wayout, beside artisan.
// It rebuilds the configuration from the server's existing .env.
// Run again after clearing META_CAPI_TEST_EVENT_CODE to end the test.
if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit(1);
}

umask(0027);
define('LARAVEL_START', microtime(true));
$logDirectory = __DIR__.'/storage/logs/testing';
if (! is_dir($logDirectory) && ! @mkdir($logDirectory, 0750, true)) {
    exit(1);
}
$write = static function (string $message) use ($logDirectory): void {
    $line = '['.gmdate('Y-m-d H:i:s').' UTC] '.$message.PHP_EOL;
    file_put_contents($logDirectory.'/meta-config.log', $line, FILE_APPEND | LOCK_EX);
    echo $line;
};

$lock = @fopen($logDirectory.'/meta-config.lock', 'c');
if (! is_resource($lock) || ! flock($lock, LOCK_EX | LOCK_NB)) {
    exit(1);
}

try {
    require __DIR__.'/vendor/autoload.php';
    $app = require __DIR__.'/bootstrap/app.php';
    $app->usePublicPath(dirname(__DIR__));
    $kernel = $app->make(\Illuminate\Contracts\Console\Kernel::class);
    $kernel->bootstrap();
    $output = new \Symfony\Component\Console\Output\BufferedOutput;
    if ($kernel->call('config:cache', ['--no-interaction' => true], $output) !== 0) {
        throw new \RuntimeException('Configuration rebuild failed');
    }
    // Read the newly generated cache, not the old in-memory configuration.
    $cached = require $app->getCachedConfigPath();
    $analytics = $cached['analytics'] ?? [];
    $meta = $analytics['meta_capi'] ?? [];
    $ready = ! empty($analytics['enabled']) && ! empty($meta['enabled'])
        && ! empty($meta['access_token'])
        && preg_match('/^\d{5,30}$/', (string) ($analytics['meta_pixel_id'] ?? ''))
        && preg_match('/^v\d+\.\d+$/', (string) ($meta['api_version'] ?? ''));
    $testCode = trim((string) ($meta['test_event_code'] ?? ''));
    $write('Cache aggiornata. CAPI configurata: '.($ready ? 'SI' : 'NO').'. Modalita: '.($testCode === '' ? 'NORMALE' : 'TEST').'.');
    if ($testCode !== '' && $testCode !== 'TEST51838') {
        $write('ATTENZIONE: il codice test configurato non corrisponde a TEST51838.');
    }
    if (! $ready) {
        exit(1);
    }
} catch (\Throwable $exception) {
    // Exception messages and console output can contain credentials: omit them.
    $write('ERRORE: cache non verificata. Classe: '.get_class($exception).'. Controllare la configurazione del server.');
    exit(1);
} finally {
    flock($lock, LOCK_UN);
    fclose($lock);
}
