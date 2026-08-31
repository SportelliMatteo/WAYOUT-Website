<?php

declare(strict_types=1);

if (PHP_SAPI !== 'cli' || count($argv) !== 3) {
    fwrite(STDERR, "Uso: php build-aruba-extractor.php <archivio.zip> <estrattore.php>\n");
    exit(1);
}

[$script, $archivePath, $extractorPath] = $argv;

if (! is_file($archivePath)) {
    fwrite(STDERR, "Archivio non trovato: {$archivePath}\n");
    exit(1);
}

$archiveName = basename($archivePath);
$archiveHash = hash_file('sha256', $archivePath);
$token = bin2hex(random_bytes(16));
$tokenHash = hash('sha256', $token);

$template = <<<'PHP'
<?php

declare(strict_types=1);

const ARCHIVE_NAME = '__ARCHIVE_NAME__';
const ARCHIVE_SHA256 = '__ARCHIVE_SHA256__';
const TOKEN_SHA256 = '__TOKEN_SHA256__';

header('Content-Type: text/html; charset=UTF-8');
header('Cache-Control: no-store, private');
header('X-Robots-Tag: noindex, nofollow, noarchive');

function page(string $title, string $message, int $status = 200): never
{
    http_response_code($status);
    $safeTitle = htmlspecialchars($title, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    $safeMessage = htmlspecialchars($message, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');

    echo "<!doctype html><html lang=\"it\"><meta charset=\"utf-8\"><meta name=\"robots\" content=\"noindex,nofollow\"><title>{$safeTitle}</title>";
    echo '<body style="max-width:760px;margin:60px auto;padding:0 24px;font:16px/1.55 system-ui;color:#111827">';
    echo "<h1>{$safeTitle}</h1><p>{$safeMessage}</p></body></html>";
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo '<!doctype html><html lang="it"><meta charset="utf-8"><meta name="robots" content="noindex,nofollow"><title>Estrazione release WAYOUT</title>';
    echo '<body style="max-width:760px;margin:60px auto;padding:0 24px;font:16px/1.55 system-ui;color:#111827"><h1>Estrazione release WAYOUT</h1>';
    echo '<p>Inserisci il token generato insieme al pacchetto. Prima dell’estrazione verrà attivata automaticamente la manutenzione.</p>';
    echo '<form method="post"><input type="password" name="token" required autocomplete="off" style="width:100%;box-sizing:border-box;padding:12px">';
    echo '<button type="submit" style="margin-top:16px;padding:12px 20px;font-weight:700">Verifica ed estrai</button></form></body></html>';
    exit;
}

$providedToken = is_string($_POST['token'] ?? null) ? $_POST['token'] : '';

if (! hash_equals(TOKEN_SHA256, hash('sha256', $providedToken))) {
    page('Accesso negato', 'Token di estrazione non valido.', 403);
}

if (! class_exists(ZipArchive::class)) {
    page('Estrazione non disponibile', 'L’estensione PHP ZipArchive non è attiva.', 500);
}

$archivePath = __DIR__.DIRECTORY_SEPARATOR.ARCHIVE_NAME;

if (! is_file($archivePath)) {
    page('Archivio mancante', 'Carica lo ZIP nella stessa cartella di questo file.', 404);
}

$actualHash = hash_file('sha256', $archivePath);

if (! is_string($actualHash) || ! hash_equals(ARCHIVE_SHA256, $actualHash)) {
    page('Verifica fallita', 'Lo SHA-256 dello ZIP non corrisponde: estrazione interrotta.', 422);
}

$zip = new ZipArchive();

if ($zip->open($archivePath) !== true) {
    page('Archivio non valido', 'Impossibile aprire lo ZIP.', 422);
}

for ($index = 0; $index < $zip->numFiles; $index++) {
    $name = str_replace('\\', '/', (string) $zip->getNameIndex($index));
    $segments = explode('/', $name);
    $isAbsolute = str_starts_with($name, '/') || preg_match('/^[A-Za-z]:\//', $name) === 1;

    if ($name === '' || str_contains($name, "\0") || $isAbsolute || in_array('..', $segments, true)) {
        $zip->close();
        page('Archivio non sicuro', 'Lo ZIP contiene un percorso non consentito.', 422);
    }

    $operationsSystem = 0;
    $attributes = 0;

    if ($zip->getExternalAttributesIndex($index, $operationsSystem, $attributes)) {
        $fileType = ($attributes >> 16) & 0170000;

        if ($fileType === 0120000) {
            $zip->close();
            page('Archivio non sicuro', 'Lo ZIP contiene un collegamento simbolico non consentito.', 422);
        }
    }
}

$maintenancePath = __DIR__.DIRECTORY_SEPARATOR.'.maintenance';

if (file_put_contents($maintenancePath, "Deploy in corso\n", LOCK_EX) === false) {
    $zip->close();
    page('Manutenzione non attivata', 'Impossibile creare il file .maintenance.', 500);
}

if (! $zip->extractTo(__DIR__)) {
    $zip->close();
    page('Estrazione fallita', 'La manutenzione resta attiva. Controlla permessi e spazio disponibile.', 500);
}

$zip->close();
page(
    'Estrazione completata',
    'La manutenzione è attiva. Ora esegui _wayout/run-deploy.php dal Cron, controlla il log e soltanto dopo elimina .maintenance, questo estrattore e lo ZIP.',
);
PHP;

$contents = str_replace(
    ['__ARCHIVE_NAME__', '__ARCHIVE_SHA256__', '__TOKEN_SHA256__'],
    [$archiveName, $archiveHash, $tokenHash],
    $template,
);

if (file_put_contents($extractorPath, $contents, LOCK_EX) === false) {
    fwrite(STDERR, "Impossibile creare l’estrattore: {$extractorPath}\n");
    exit(1);
}

chmod($extractorPath, 0644);
fwrite(STDOUT, "Token estrazione: {$token}\n");
