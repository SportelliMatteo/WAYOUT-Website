<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Throwable;

class ProductionCheck extends Command
{
    protected $signature = 'app:production-check';

    protected $description = 'Verifica che le impostazioni di sicurezza essenziali siano pronte per la produzione';

    public function handle(): int
    {
        $checks = [
            ['Ambiente production', app()->environment('production')],
            ['Debug disattivato', config('app.debug') === false],
            ['APP_URL usa HTTPS', str_starts_with((string) config('app.url'), 'https://')],
            ['APP_KEY configurata', Str::startsWith((string) config('app.key'), 'base64:')],
            ['Host consentiti configurati', config('security.trusted_hosts', []) !== []],
            ['Header di sicurezza attivi', config('security.headers_enabled') === true],
            ['CSP attiva', config('security.csp_enabled') === true],
            ['HSTS attivo', config('security.hsts_enabled') === true],
            ['Cookie sessione cifrato', config('session.encrypt') === true],
            ['Cookie sessione Secure', config('session.secure') === true],
            ['Cookie sessione HttpOnly', config('session.http_only') === true],
            ['PHP 8.4.1 o successivo', version_compare(PHP_VERSION, '8.4.1', '>=')],
            ['Estensioni PHP richieste', $this->extensionsAreLoaded()],
            ['Database MySQL', config('database.default') === 'mysql'],
            ['MySQL Aruba configurato', $this->mysqlIsConfigured()],
            ['Database raggiungibile', $this->databaseIsReachable()],
            ['Schema applicativo migrato', $this->schemaIsReady()],
            ['Coda sincrona', config('queue.default') === 'sync'],
            ['Sessioni su file', config('session.driver') === 'file'],
            ['Cache su file', config('cache.default') === 'file'],
            ['Invio email attivo', config('email.enabled') === true],
            ['Brevo SMTP configurato', config('mail.default') === 'smtp' && filled(config('mail.mailers.smtp.host')) && filled(config('mail.mailers.smtp.password'))],
            ['Backend WAYOUT HTTPS configurato', str_starts_with((string) config('services.wayout.base_url'), 'https://')],
            ['Segreto interno WAYOUT robusto', strlen((string) config('services.wayout.internal_secret')) >= 32],
            ['Firebase Phone Auth configurato', collect(config('services.firebase.client', []))->every(static fn ($value): bool => filled($value)) && filled(config('services.firebase.project_id'))],
            ['Segreto API benefici robusto', filled(config('benefits.server_auth.key')) && strlen((string) config('benefits.server_auth.secret')) >= 32],
            ['Analytics debug disattivato', config('analytics.debug') === false],
            ['Artefatti Vite dev assenti', $this->developmentArtifactsAreAbsent()],
            ['Build frontend presente', $this->frontendBuildExists()],
            ['Release Aruba identificata', filled(config('aruba.release_id'))],
            ['Directory Laravel scrivibili', is_writable(storage_path()) && is_writable(base_path('bootstrap/cache'))],
            ['Qonto coerente', ! config('services.qonto.invoicing_enabled') || (config('services.qonto.environment') === 'production' && blank(config('services.qonto.staging_token')))],
        ];

        $this->table(
            ['Controllo', 'Esito'],
            array_map(static fn (array $check): array => [$check[0], $check[1] ? 'OK' : 'ERRORE'], $checks),
        );

        $failures = count(array_filter($checks, static fn (array $check): bool => ! $check[1]));

        if ($failures > 0) {
            $this->error("Produzione non pronta: {$failures} controlli non superati.");

            return self::FAILURE;
        }

        $this->info('Configurazione di produzione pronta. Eseguire comunque test, migrazioni e verifica HTTPS sul server.');

        return self::SUCCESS;
    }

    private function extensionsAreLoaded(): bool
    {
        return collect(['curl', 'dom', 'fileinfo', 'json', 'mbstring', 'openssl', 'pdo_mysql'])
            ->every(static fn (string $extension): bool => extension_loaded($extension));
    }

    private function mysqlIsConfigured(): bool
    {
        $connection = config('database.connections.mysql', []);

        return filled($connection['host'] ?? null)
            && filled($connection['database'] ?? null)
            && filled($connection['username'] ?? null)
            && filled($connection['password'] ?? null)
            && filter_var($connection['port'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1, 'max_range' => 65535]]) !== false
            && ($connection['engine'] ?? null) === 'InnoDB';
    }

    /** @return list<string> */
    private function publicDirectories(): array
    {
        $directories = [public_path()];

        if (is_file(base_path('RELEASE_ID'))) {
            $directories[] = dirname(base_path());
        }

        return array_values(array_unique($directories));
    }

    private function developmentArtifactsAreAbsent(): bool
    {
        return collect($this->publicDirectories())->every(
            static fn (string $directory): bool => ! is_file($directory.'/hot')
                && ! is_file($directory.'/fonts-manifest.dev.json'),
        );
    }

    private function frontendBuildExists(): bool
    {
        return collect($this->publicDirectories())->contains(
            static fn (string $directory): bool => is_file($directory.'/build/manifest.json'),
        );
    }

    private function databaseIsReachable(): bool
    {
        try {
            return DB::connection()->getPdo() !== null;
        } catch (Throwable) {
            return false;
        }
    }

    private function schemaIsReady(): bool
    {
        try {
            return collect([
                'waitlist_entries',
                'purchases',
                'consent_events',
                'admin_users',
                'cookie_consent_events',
            ])->every(static fn (string $table): bool => Schema::hasTable($table));
        } catch (Throwable) {
            return false;
        }
    }
}
