<?php

namespace App\Console\Commands;

use App\Models\AdminUser;
use App\Support\DatabaseUuid;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ResetAdminOtp extends Command
{
    protected $signature = 'admin:reset-otp
        {email? : Email del singolo account amministrativo}
        {--all : Resetta il TOTP di tutti gli amministratori}
        {--force : Non chiedere conferma}';

    protected $description = 'Disattiva il TOTP di uno o di tutti gli amministratori e invalida le relative sessioni';

    public function handle(): int
    {
        $email = $this->argument('email');
        $all = (bool) $this->option('all');

        if (($email === null && ! $all) || ($email !== null && $all)) {
            $this->error('Specifica una email oppure usa --all, ma non entrambe.');

            return self::FAILURE;
        }

        $admins = $all
            ? AdminUser::query()->orderBy('email')->get()
            : AdminUser::query()->where('email', strtolower((string) $email))->get();

        if ($admins->isEmpty()) {
            $this->error($all ? 'Nessun account amministrativo trovato.' : 'Account amministrativo non trovato.');

            return self::FAILURE;
        }

        $description = $all
            ? "Resettare il TOTP di tutti i {$admins->count()} amministratori?"
            : "Resettare il TOTP di {$admins->first()->email}?";

        if (! $this->option('force') && ! $this->confirm($description)) {
            $this->warn('Operazione annullata.');

            return self::SUCCESS;
        }

        DB::transaction(function () use ($admins) {
            foreach ($admins as $admin) {
                $wasConfigured = (bool) $admin->totp_confirmed_at;

                $admin->forceFill([
                    'totp_secret' => null,
                    'totp_confirmed_at' => null,
                    'last_totp_step' => null,
                    'recovery_codes' => null,
                    'auth_version' => $admin->auth_version + 1,
                ])->save();

                DB::table('admin_audit_events')->insert([
                    'id' => DatabaseUuid::new(),
                    'admin_user_id' => $admin->id,
                    'actor_name' => 'Server console',
                    'actor_email' => 'console@localhost',
                    'action' => 'admin_user.otp_reset_console',
                    'target_type' => 'admin_user',
                    'target_id' => $admin->id,
                    'target_label' => $admin->email,
                    'old_values' => json_encode(['totp_configured' => $wasConfigured], JSON_THROW_ON_ERROR),
                    'new_values' => json_encode(['totp_configured' => false], JSON_THROW_ON_ERROR),
                    'occurred_at' => now(),
                    'created_at' => now(),
                ]);
            }
        });

        Log::warning('Admin TOTP reset from console.', [
            'admin_user_ids' => $admins->pluck('id')->all(),
            'all_accounts' => $all,
        ]);
        $this->info($all
            ? "TOTP resettato per {$admins->count()} account. Tutte le sessioni precedenti sono state invalidate."
            : 'TOTP resettato. Al prossimo accesso sarà richiesta una nuova configurazione.');

        return self::SUCCESS;
    }
}
