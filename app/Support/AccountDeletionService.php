<?php

namespace App\Support;

use App\Exceptions\WayoutApiException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class AccountDeletionService
{
    public const WARNING = 'Procedendo alla cancellazione dell’account perderai tutte le partecipazioni agli eventi, tutte le richieste di partecipazione e tutte le creazioni effettuate, inclusi gli eventi e i tavoli creati.';

    public function __construct(private readonly WayoutApiClient $client) {}

    public function submit(Request $request, string $id, string $name, string $email, string $phone): string
    {
        $lock = Cache::lock('account-deletion:'.$id, 60);
        if (! $lock->get()) {
            throw new WayoutApiException('Richiesta già in elaborazione.', 409);
        }

        try {
            DB::table('account_deletion_requests')->insertOrIgnore([
                'id' => $id, 'email_hash' => PrivacySafeLogContext::fingerprint($email),
                'status' => 'pending', 'acknowledged_at' => now(), 'warning_hash' => hash('sha256', self::WARNING),
                'created_at' => now(), 'updated_at' => now(),
            ]);
            $record = DB::table('account_deletion_requests')->where('id', $id)->first();
            if (! hash_equals($record->email_hash, PrivacySafeLogContext::fingerprint($email))) {
                throw new WayoutApiException('Usa l’email indicata nel primo invio della richiesta.', 409);
            }
            if ($record->backend_ticket_id) {
                Log::info('Account deletion support ticket reused for duplicate submission.', [
                    'request_id' => $id, 'backend_ticket_id' => $record->backend_ticket_id,
                ]);
                return $record->backend_ticket_id;
            }
            if (in_array($record->status, ['sending', 'uncertain'], true)) {
                throw new WayoutApiException('Invio in verifica. Contatta l’assistenza con il riferimento '.$id.'.', 503);
            }
            DB::table('account_deletion_requests')->where('id', $id)->update(['status' => 'sending', 'updated_at' => now()]);
            try {
                $response = $this->client->internalPost('/api/v1/internal/support/tickets', [
                    'category' => 'CANCELLAZIONE_ACCOUNT', 'title' => 'Richiesta di cancellazione dell’account',
                    'message' => 'Chiedo la cancellazione del mio account WAYOUT associato all’indirizzo '.$email
                        .".\n\nTelefono di contatto: ".$phone
                        ."\n\nDichiaro di aver letto e compreso il seguente avviso: ".self::WARNING
                        ."\n\nConferma dell’avviso registrata il: ".$record->acknowledged_at
                        ."\nRiferimento richiesta sito: ".$id,
                    'priority' => 'NORMAL', 'contact_email' => $email, 'contact_name' => $name,
                    'context' => [
                        'source' => 'account-deletion', 'page_url' => route('account.deletion'),
                        'client_ip' => $request->ip(), 'client_user_agent' => mb_substr($request->userAgent() ?? '', 0, 512),
                        'locale' => 'it-IT',
                    ],
                ]);
                $ticketId = data_get($response, 'data.id');
                if (($response['success'] ?? false) !== true || ! is_string($ticketId) || ! Str::isUuid($ticketId)) {
                    throw new WayoutApiException('Risposta del servizio assistenza non valida.', 502);
                }
            } catch (WayoutApiException $exception) {
                DB::table('account_deletion_requests')->where('id', $id)->update([
                    'status' => $exception->status >= 500 ? 'uncertain' : 'failed', 'updated_at' => now(),
                ]);
                throw $exception;
            }
            DB::table('account_deletion_requests')->where('id', $id)->update([
                'status' => 'sent', 'backend_ticket_id' => $ticketId, 'updated_at' => now(),
            ]);
            Log::info('Account deletion support ticket confirmed by backend.', [
                'request_id' => $id, 'backend_ticket_id' => $ticketId,
                'backend_host' => parse_url((string) config('services.wayout.base_url'), PHP_URL_HOST),
            ]);

            return $ticketId;
        } finally {
            $lock->release();
        }
    }
}
