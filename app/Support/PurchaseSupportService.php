<?php

namespace App\Support;

use App\Exceptions\WayoutApiException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PurchaseSupportService
{
    public const WITHDRAWAL = 'promo-withdrawal';

    public const NON_RENEWAL = 'subscription-non-renewal';

    public const CONTACT = 'contact';

    public const CONTACT_CATEGORIES = [
        'club' => 'CLUB_E_VENUE',
        'collaboration' => 'COLLABORAZIONI',
        'information' => 'CURIOSITA_E_INFORMAZIONI',
        'other' => 'ALTRO',
    ];

    public function __construct(private readonly WayoutApiClient $client) {}

    public function submit(Request $request, string $id, string $source, string $name, string $email, string $message, ?string $contactSubject = null): string
    {
        [$category, $title, $pageRoute] = match ($source) {
            self::WITHDRAWAL => ['RECESSO_E_RIMBORSO', 'Recesso e richiesta rimborso pacchetto promo', 'legal.refunds'],
            self::NON_RENEWAL => ['DISDETTA_ABBONAMENTO', 'Disdetta abbonamento e richiesta di non rinnovo', 'subscription.cancel'],
            self::CONTACT => [self::CONTACT_CATEGORIES[$contactSubject] ?? throw new \InvalidArgumentException('Oggetto contatto non valido.'), __('messages.contact.subject_options.'.$contactSubject), 'contact'],
            default => throw new \InvalidArgumentException('Sorgente ticket non valida.'),
        };
        $lock = Cache::lock('purchase-support:'.$id, 60);
        if (! $lock->get()) {
            throw new WayoutApiException('La richiesta è già in elaborazione. Attendi e riprova.', 409);
        }

        try {
            DB::table('purchase_support_requests')->insertOrIgnore([
                'id' => $id, 'purchase_id' => null, 'source' => $source,
                'email_hash' => PrivacySafeLogContext::fingerprint($email),
                'status' => 'pending', 'created_at' => now(), 'updated_at' => now(),
            ]);
            $record = DB::table('purchase_support_requests')->where('id', $id)->first();
            if ($record->source !== $source || ! hash_equals((string) $record->email_hash, PrivacySafeLogContext::fingerprint($email))) {
                throw new WayoutApiException('Il modulo non corrisponde alla richiesta originale.', 409);
            }
            if ($record->backend_ticket_id) {
                return $record->backend_ticket_id;
            }
            if (in_array($record->status, ['sending', 'uncertain'], true)) {
                throw new WayoutApiException('L’esito dell’invio è in verifica. Contatta l’assistenza indicando il riferimento '.$record->id.'. Non è necessario inviare di nuovo la richiesta.', 503);
            }
            DB::table('purchase_support_requests')->where('id', $record->id)->update(['status' => 'sending', 'updated_at' => now()]);
            try {
                $response = $this->client->internalPost('/api/v1/internal/support/tickets', [
                    'category' => $category,
                    'title' => $title,
                    'message' => $message."\n\nRiferimento richiesta sito: ".$record->id,
                    'priority' => 'NORMAL', 'contact_email' => $email, 'contact_name' => $name,
                    'context' => [
                        'source' => $source,
                        'page_url' => route($pageRoute),
                        'client_ip' => $request->ip(), 'client_user_agent' => mb_substr($request->userAgent() ?? '', 0, 512),
                        'locale' => 'it-IT',
                    ],
                ]);
                $ticketId = data_get($response, 'data.id');
                if (($response['success'] ?? false) !== true || ! is_string($ticketId) || ! Str::isUuid($ticketId)) {
                    throw new WayoutApiException('Risposta del servizio assistenza non valida.', 502);
                }
            } catch (WayoutApiException $exception) {
                DB::table('purchase_support_requests')->where('id', $record->id)->update([
                    'status' => $exception->status >= 500 ? 'uncertain' : 'failed', 'updated_at' => now(),
                ]);
                throw $exception;
            }
            DB::table('purchase_support_requests')->where('id', $record->id)->update([
                'status' => 'sent', 'backend_ticket_id' => $ticketId, 'updated_at' => now(),
            ]);

            return $ticketId;
        } finally {
            $lock->release();
        }
    }
}
