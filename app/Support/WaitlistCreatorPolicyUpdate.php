<?php

namespace App\Support;

use RuntimeException;

final class WaitlistCreatorPolicyUpdate
{
    public static function apply(string $content, string $key, string $locale): string
    {
        if ($key === 'passes') {
            return self::updatePassSections($content, $locale);
        }

        foreach (self::replacements($key, $locale) as [$old, $new]) {
            $newPattern = '~'.str_replace("\n", '\r?\n', preg_quote($new, '~')).'~u';
            if (preg_match($newPattern, $content)) {
                continue;
            }

            $oldPattern = '~'.str_replace("\n", '\r?\n', preg_quote($old, '~')).'~u';
            if (! preg_match($oldPattern, $content)) {
                throw new RuntimeException('Unable to locate the Waitlist Pass section in '.$key.' ('.$locale.').');
            }

            $content = preg_replace_callback($oldPattern, fn (array $match): string => str_contains($match[0], "\r\n")
                ? str_replace("\n", "\r\n", $new)
                : $new, $content, 1);
        }

        return $content;
    }

    private static function updatePassSections(string $content, string $locale): string
    {
        $it = $locale === 'it';
        $include = $it ? 'Include: ' : 'Includes: ';
        $exclude = $it ? 'Non include: ' : 'Not included: ';
        $question = $it ? 'Posso creare tavoli con il Waitlist Pass o con Founder Join?' : 'Can I create tables with the Waitlist Pass or with Founder Join?';
        $creator = $it ? 'Funzioni Creator' : 'Functions Creator';
        $patterns = [
            '~'.($it ? 'Il Waitlist Pass è un possibile beneficio gratuito di 60 giorni' : 'The Waitlist Pass is a (?:potential )?free 60-day benefit').'[^<.]*\.~u',
            '~WAITLIST PASS</strong></p>(?:(?!FOUNDER JOIN).)*?<p><em><strong>'.preg_quote($include, '~').'</strong></em>\K[^<]*~su',
            '~WAITLIST PASS</strong></p>(?:(?!FOUNDER JOIN).)*?<p><em><strong>'.preg_quote($exclude, '~').'</strong></em>\K[^<]*~su',
            '~'.preg_quote($question, '~').'</p>\s*<p[^>]*>\K[^<]*~u',
            '~<tr>\s*<td[^>]*><em><strong>'.$creator.'</strong></em></td>.*?</tr>~su',
            '~<h3[^>]*>'.($it ? 'Cosa include' : 'What includes').'</h3>\s*<ul.*?</ul>~su',
            '~<h3[^>]*>'.($it ? 'Cosa non include' : 'What does not include').'</h3>\s*<ul.*?</ul>~su',
        ];

        foreach (self::replacements('passes', $locale) as $index => [$old, $new]) {
            $content = preg_replace_callback($patterns[$index], function (array $match) use ($index, $new, $it): string {
                if ($index === 4) {
                    return preg_replace('~(</strong></em></td>\s*<td[^>]*>)[^<]*~u', '${1}'.($it ? 'Sì' : 'Yes'), $match[0], 1);
                }

                return str_contains($match[0], "\r\n") ? str_replace("\n", "\r\n", $new) : $new;
            }, $content, 1, $count);

            if ($count !== 1) {
                throw new RuntimeException('Unable to locate Waitlist Pass section '.$index.' ('.$locale.').');
            }
        }

        return $content;
    }

    /** @return list<array{string, string}> */
    public static function replacements(string $key, string $locale): array
    {
        return match ($key.'.'.$locale) {
            'terms.it' => [
                ['<li>“Waitlist Pass”: l’eventuale beneficio gratuito di durata limitata, con sole funzionalità Join, riservato agli Utenti validi che soddisfano le condizioni indicate all’articolo 7;</li>', '<li>“Waitlist Pass”: l’eventuale beneficio promozionale gratuito di durata limitata che comprende le funzionalità Join e Creator rese disponibili nell’app, riservato agli Utenti validi che soddisfano le condizioni indicate all’articolo 7.</li>'],
                ['<p class="mt-3">7.2 Il Waitlist Pass ha durata di 60 giorni dalla corretta attivazione individuale nell’app, che potrà avvenire a partire dal go-live. Il beneficio comprende esclusivamente le funzionalità Join indicate nell’app e nelle relative condizioni; non comprende la creazione o la gestione di tavoli e non attribuisce funzionalità Creator.</p>', '<p class="mt-3">7.2 Il Waitlist Pass ha durata di 60 giorni dalla corretta attivazione individuale nell’app, che potrà avvenire a partire dal go-live. Durante il periodo di validità, il beneficio comprende le funzionalità Join e le funzionalità Creator rese disponibili nell’app, inclusa la possibilità di creare e gestire tavoli digitali e le relative richieste di partecipazione, secondo le regole, i limiti, le condizioni di utilizzo, le misure di sicurezza e moderazione applicabili al servizio.</p>'],
            ],
            'passes.it' => [
                ['Il Waitlist Pass è un possibile beneficio gratuito di 60 giorni con sole funzioni Join.', 'Il Waitlist Pass è un possibile beneficio gratuito di 60 giorni con funzionalità Join e Creator.'],
                ['Funzionalità Join · Richieste di partecipazione · Chat dopo l’accettazione', 'Funzionalità Join e Creator · Richieste di partecipazione · Chat dopo l’accettazione · Creazione e gestione dei tavoli e delle relative richieste.'],
                ['Creazione e gestione dei tavoli · Funzionalità Creator', 'Servizi, prenotazioni, ingressi, consumazioni o altri servizi forniti da locali o terzi · Garanzia di accettazione o di disponibilità di specifici tavoli/eventi.'],
                ['No. Entrambi comprendono soltanto le funzionalità Join. Per creare e gestire tavoli serve Founder Creator 12M o un altro piano Creator disponibile.', 'Sì con il Waitlist Pass; no con Founder Join. Durante i 60 giorni di validità, il Waitlist Pass comprende anche le funzionalità Creator e consente quindi di creare e gestire tavoli secondo le regole e i limiti del servizio. Founder Join 12M comprende invece esclusivamente le funzionalità Join.'],
                ['<tr>
                        <td class="px-4 py-3 align-top font-black text-slate-900"><em><strong>Funzioni Creator</strong></em></td>
                        <td class="px-4 py-3 align-top">No</td>
                        <td class="px-4 py-3 align-top">No</td>
                        <td class="px-4 py-3 align-top">Sì</td>
                    </tr>', '<tr>
                        <td class="px-4 py-3 align-top font-black text-slate-900"><em><strong>Funzioni Creator</strong></em></td>
                        <td class="px-4 py-3 align-top">Sì</td>
                        <td class="px-4 py-3 align-top">No</td>
                        <td class="px-4 py-3 align-top">Sì</td>
                    </tr>'],
                ['<h3 class="mt-6 text-xl font-black text-slate-900">Cosa include</h3>
        <ul class="mt-3 list-disc space-y-2 pl-6">
            <li>Le funzionalità Join indicate nell’app.</li>
            <li>La possibilità di scoprire tavoli o occasioni sociali disponibili.</li>
            <li>La possibilità di inviare richieste di partecipazione secondo le regole e i limiti del servizio.</li>
            <li>L’accesso alle chat dei tavoli per i quali la richiesta è stata accettata.</li>
        </ul>', '<h3 class="mt-6 text-xl font-black text-slate-900">Cosa include</h3>
        <ul class="mt-3 list-disc space-y-2 pl-6">
            <li>Tutte le funzionalità Join e Creator rese disponibili nell’app.</li>
            <li>Discovery, invio di richieste di partecipazione e chat dopo l’accettazione.</li>
            <li>Creazione e gestione di tavoli digitali e delle relative richieste, sempre secondo regole e limiti del servizio.</li>
        </ul>'],
                ['<h3 class="mt-6 text-xl font-black text-slate-900">Cosa non include</h3>
        <ul class="mt-3 list-disc space-y-2 pl-6">
            <li>La creazione o la gestione di tavoli.</li>
            <li>Funzionalità Creator.</li>
            <li>Ingresso nei locali, prenotazioni, tavoli fisici, drink, consumazioni o servizi di terzi.</li>
            <li>La garanzia di essere accettati in un tavolo o di trovare tavoli in una data o località specifica.</li>
        </ul>', '<h3 class="mt-6 text-xl font-black text-slate-900">Cosa non include</h3>
        <ul class="mt-3 list-disc space-y-2 pl-6">
            <li>Ingresso nei locali, prenotazioni, tavoli fisici, ticket, drink, consumazioni o servizi di terzi.</li>
            <li>Garanzia di essere accettati in uno specifico tavolo.</li>
            <li>Diritto assoluto o illimitato a creare tavoli, che restano soggetti a limiti di pubblicazione, capienza, frequenza, fair use, sicurezza, moderazione e regole della community.</li>
        </ul>'],
            ],
            'terms.en' => [
                ['<li>“Waitlist Pass”: any free, limited-duration benefit with Join functionality only, reserved for valid users who meet the conditions set out in Article 7;</li>', '<li>“Waitlist Pass”: the potential free promotional benefit of limited duration that includes the Join and Creator features made available in the app, reserved for valid Users who meet the conditions set out in Article 7.</li>'],
                ['<p class="mt-3">7.2 The Waitlist Pass lasts 60 days from the correct individual activation in the app, which can take place from the go-live. The benefit includes only the Join features indicated in the app and its conditions; does not include creating or managing tables and does not attribute Creator functionality.</p>', '<p class="mt-3">7.2 The Waitlist Pass lasts 60 days from successful individual activation in the app, which can take place from go-live. During its validity period, the benefit includes the Join and Creator features made available in the app, including the ability to create and manage digital tables and their participation requests, subject to the rules, limits, terms of use, security and moderation measures applicable to the service.</p>'],
            ],
            'passes.en' => [
                ['The Waitlist Pass is a free 60-day benefit with only Join functions.', 'The Waitlist Pass is a potential free 60-day benefit with Join and Creator features.'],
                ['Join functionality · Participation requests · Chat after acceptance', 'Join and Creator features · Participation requests · Chat after acceptance · Creating and managing tables and their participation requests.'],
                ['Creating and managing tables · Functionality Creator', 'Services, reservations, admission, drinks or other services provided by venues or third parties · Guaranteed acceptance or availability of specific tables/events.'],
                ['No. Both include only Join features. To create and manage tables you need Founder Creator 12M or another available Creator plan.', 'Yes with the Waitlist Pass; no with Founder Join. During its 60-day validity period, the Waitlist Pass also includes Creator features and therefore allows you to create and manage tables according to the rules and limits of the service. Founder Join 12M includes Join features only.'],
                ['<tr>
                        <td class="px-4 py-3 align-top font-black text-slate-900"><em><strong>Functions Creator</strong></em></td>
                        <td class="px-4 py-3 align-top">No.</td>
                        <td class="px-4 py-3 align-top">No.</td>
                        <td class="px-4 py-3 align-top">Yes</td>
                    </tr>', '<tr>
                        <td class="px-4 py-3 align-top font-black text-slate-900"><em><strong>Functions Creator</strong></em></td>
                        <td class="px-4 py-3 align-top">Yes</td>
                        <td class="px-4 py-3 align-top">No.</td>
                        <td class="px-4 py-3 align-top">Yes</td>
                    </tr>'],
                ['<h3 class="mt-6 text-xl font-black text-slate-900">What includes</h3>
        <ul class="mt-3 list-disc space-y-2 pl-6">
            <li>Join features listed in the app.</li>
            <li>The possibility to discover tables or social occasions available.</li>
            <li>The possibility to send requests for participation according to the rules and limits of the service.</li>
            <li>Access to the chat tables for which the request was accepted.</li>
        </ul>', '<h3 class="mt-6 text-xl font-black text-slate-900">What includes</h3>
        <ul class="mt-3 list-disc space-y-2 pl-6">
            <li>All Join and Creator features made available in the app.</li>
            <li>Discovery, sending participation requests and chatting after acceptance.</li>
            <li>Creating and managing digital tables and their participation requests, always subject to the rules and limits of the service.</li>
        </ul>'],
                ['<h3 class="mt-6 text-xl font-black text-slate-900">What does not include</h3>
        <ul class="mt-3 list-disc space-y-2 pl-6">
            <li>The creation or management of tables.</li>
            <li>Creator functionality.</li>
            <li>Entrance to the premises, bookings, physical tables, drinks, consummations or third-party services.</li>
            <li>The guarantee to be accepted in a table or to find tables in a specific date or location.</li>
        </ul>', '<h3 class="mt-6 text-xl font-black text-slate-900">What does not include</h3>
        <ul class="mt-3 list-disc space-y-2 pl-6">
            <li>Admission to venues, reservations, physical tables, tickets, drinks, food or third-party services.</li>
            <li>Guaranteed acceptance at a specific table.</li>
            <li>An absolute or unlimited right to create tables, which remain subject to publication, capacity, frequency, fair use, security, moderation and community rules.</li>
        </ul>'],
            ],
            default => throw new RuntimeException('Unsupported Waitlist Pass document.'),
        };
    }
}
