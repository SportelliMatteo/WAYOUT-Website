<?php

namespace App\Support;

final class MetaTrackingPolicyUpdate
{
    public static function apply(string $content, string $locale): string
    {
        $replacements = $locale === 'it' ? [
            'Meta Pixel esclusivamente lato browser' => 'Meta Pixel lato browser e, quando configurata, la Conversions API lato server',
            'Meta Pixel lato browser.' => 'Meta Pixel lato browser e, quando configurata, la Conversions API lato server.',
            'per Meta Pixel lato browser,' => 'per Meta Pixel e Conversions API,',
            'Alla data di questa versione, Advanced Matching e Conversions API sono disattivati.' => 'Advanced Matching automatico del Pixel resta disattivato. La Conversions API, quando configurata, è subordinata al consenso Marketing.',
            'Alla data di questa versione Advanced Matching e Conversions API sono disattivati. Eventuali future attivazioni richiederanno un aggiornamento preventivo della documentazione e, quando necessario, una nuova scelta dell’utente.' => 'Advanced Matching automatico del Pixel resta disattivato. La Conversions API può affiancare il Pixel dopo una nuova scelta relativa al consenso Marketing.',
        ] : [
            'Meta Pixel exclusively browser side' => 'Meta Pixel in the browser and, when configured, the server-side Conversions API',
            'Meta Pixel browser side.' => 'Meta Pixel in the browser and, when configured, the server-side Conversions API.',
            'for Meta Pixel browser side,' => 'for Meta Pixel and Conversions API,',
            'At the date of this version, Advanced Matching and Conversions API are disabled.' => 'Automatic Pixel Advanced Matching remains disabled. When configured, the Conversions API requires Marketing consent.',
            'At the date of this version Advanced Matching and Conversions API are disabled. Any future activations will require a prior update of the documentation and, when necessary, a new user choice.' => 'Automatic Pixel Advanced Matching remains disabled. The Conversions API may complement the Pixel following a renewed Marketing consent choice.',
        ];
        $content = str_replace(array_keys($replacements), array_values($replacements), $content);
        if (str_contains($content, 'meta-conversions-api')) {
            return $content;
        }
        $paragraph = $locale === 'it'
            ? '<section class="meta-conversions-api mt-8"><h2 class="text-xl font-black">Conversions API di Meta</h2><p class="mt-3">Quando attivata nella configurazione del sito, la Conversions API invia a Meta gli eventi di conferma della waitlist e di acquisto confermato dal server, esclusivamente previo consenso Marketing valido, distinto dal consenso alle email promozionali. I dati possono comprendere email normalizzata e trasformata con SHA-256, indirizzo IP, user agent, identificativi dei cookie _fbp e _fbc se disponibili, nome, ora e identificativo dell’evento, pagina di origine senza token o parametri riservati e, per gli acquisti, valore e valuta. L’hashing dell’email non rende i dati anonimi. Pixel e server condividono un identificativo per evitare il doppio conteggio. I dati in attesa di invio sono conservati cifrati per un massimo di sette giorni e il contenuto dell’evento viene eliminato dopo l’invio; le verifiche e la pulizia avvengono tramite le attività pianificate del sito. Il consenso viene ricontrollato prima di ogni tentativo: la revoca dalle preferenze cookie impedisce gli invii successivi associati alla scelta revocata. Restano applicabili le finalità, i destinatari, i trasferimenti e i diritti descritti nella Privacy policy.</p></section>'
            : '<section class="meta-conversions-api mt-8"><h2 class="text-xl font-black">Meta Conversions API</h2><p class="mt-3">When enabled in the website configuration, the Conversions API sends verified waitlist registrations and server-confirmed purchases to Meta only with valid prior Marketing consent, separate from consent to promotional emails. Data may include a normalized SHA-256-hashed email, IP address, user agent, _fbp and _fbc cookie identifiers when available, event name, time and identifier, a source page without tokens or confidential parameters and, for purchases, value and currency. Hashing does not make the email anonymous. Pixel and server share an event identifier to prevent double counting. Pending data is stored encrypted for up to seven days and event payloads are removed after delivery; checks and cleanup run through the website scheduler. Consent is checked again before every attempt: withdrawal through cookie preferences prevents subsequent deliveries associated with that consent. The purposes, recipients, transfers and rights described in the Privacy policy also apply.</p></section>';

        return $content."\n".$paragraph;
    }
}
