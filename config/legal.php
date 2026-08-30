<?php

return [
    /*
     * Questi valori servono solo per la prima importazione nel database.
     * Dopo l'importazione, testi e versioni correnti sono gestiti dalla dashboard admin.
     */
    'documents' => [
        'privacy' => [
            'group' => 'policies',
            'initial_version' => '1.0',
            'view' => 'pages/legal/privacy.blade.php',
            'views' => ['en' => 'pages/legal/en/privacy.blade.php'],
            'route' => 'legal.privacy',
            'titles' => ['it' => 'Privacy policy', 'en' => 'Privacy policy'],
            'descriptions' => [
                'it' => 'Informativa sul trattamento dei dati personali degli utenti del sito WAYOUT.',
                'en' => 'Information on the processing of personal data of Wayout website users.',
            ],
        ],
        'cookies' => [
            'group' => 'policies',
            'initial_version' => '1.0',
            'view' => 'pages/legal/cookies.blade.php',
            'views' => ['en' => 'pages/legal/en/cookies.blade.php'],
            'route' => 'legal.cookies',
            'titles' => ['it' => 'Cookie policy', 'en' => 'Cookie policy'],
            'descriptions' => [
                'it' => 'Informativa sull’uso di cookie e strumenti simili sul sito WAYOUT.',
                'en' => 'Information about cookies and similar technologies used on the Wayout website.',
            ],
        ],
        'terms' => [
            'group' => 'policies',
            'initial_version' => '1.0',
            'view' => 'pages/legal/terms.blade.php',
            'views' => ['en' => 'pages/legal/en/terms.blade.php'],
            'route' => 'legal.terms',
            'titles' => ['it' => 'Termini sito e waitlist', 'en' => 'Website and waitlist terms'],
            'descriptions' => [
                'it' => 'Termini di utilizzo del sito WAYOUT e della waitlist pre-lancio.',
                'en' => 'Terms for using the WAYOUT website and pre-launch waitlist.',
            ],
        ],
        'passes' => [
            'group' => 'commerce',
            'initial_version' => '1.0',
            'view' => 'pages/legal/passes.blade.php',
            'views' => ['en' => 'pages/legal/en/passes.blade.php'],
            'route' => 'legal.passes',
            'titles' => ['it' => 'Come funzionano i Pass', 'en' => 'How Passes work'],
            'descriptions' => [
                'it' => 'Waitlist Pass, Founder Join 12M e Founder Creator 12M: cosa includono, quando si attivano e quali condizioni si applicano.',
                'en' => 'Conditions, start date and limitations of WAYOUT Waitlist and Founder Passes.',
            ],
        ],
        'sales' => [
            'group' => 'commerce',
            'initial_version' => '1.0',
            'view' => 'pages/legal/sales.blade.php',
            'views' => ['en' => 'pages/legal/en/sales.blade.php'],
            'route' => 'legal.sales',
            'titles' => ['it' => 'Termini di vendita', 'en' => 'Terms of sale'],
            'descriptions' => [
                'it' => 'Condizioni generali applicabili alla formazione dell’ordine, al pagamento e alla gestione dell’acquisto online dei Founder Pass WAYOUT.',
                'en' => 'Terms for purchasing WAYOUT Founder Passes online.',
            ],
        ],
        'presale' => [
            'group' => 'commerce',
            'initial_version' => '1.0',
            'view' => 'pages/legal/presale.blade.php',
            'views' => ['en' => 'pages/legal/en/presale.blade.php'],
            'route' => 'legal.presale',
            'titles' => ['it' => 'Condizioni di pre-sale', 'en' => 'Pre-sale terms'],
            'descriptions' => [
                'it' => 'Condizioni applicabili agli acquisti effettuati durante la fase di pre-sale dei Founder Pass WAYOUT.',
                'en' => 'Specific terms for the pre-sale of WAYOUT Founder Passes.',
            ],
        ],
        'refunds' => [
            'group' => 'commerce',
            'initial_version' => '1.0',
            'view' => 'pages/legal/refunds.blade.php',
            'views' => ['en' => 'pages/legal/en/refunds.blade.php'],
            'route' => 'legal.refunds',
            'titles' => ['it' => 'Refund Policy', 'en' => 'Refund Policy'],
            'descriptions' => [
                'it' => 'Documento pubblico destinato al sito wayoutapp.it e al flusso di acquisto dei Founder Pass WAYOUT.',
                'en' => 'Rules on legal and contractual refunds for WAYOUT purchases.',
            ],
        ],
        'withdrawal_info' => [
            'group' => 'commerce',
            'initial_version' => '1.0',
            'view' => 'pages/legal/withdrawal-info.blade.php',
            'views' => ['en' => 'pages/legal/en/withdrawal-info.blade.php'],
            'route' => 'legal.refunds',
            'titles' => ['it' => 'Informativa sul diritto di recesso', 'en' => 'Withdrawal right information'],
            'descriptions' => [
                'it' => 'Documento pubblico da rendere disponibile prima dell’acquisto, nel checkout, nella conferma d’ordine e nella pagina Recesso e rimborsi del sito wayoutapp.it.',
                'en' => 'Information about withdrawal rights for WAYOUT purchases.',
            ],
        ],
        'notice' => [
            'group' => 'policies',
            'initial_version' => '1.0',
            'view' => 'pages/legal/notice.blade.php',
            'views' => ['en' => 'pages/legal/en/notice.blade.php'],
            'route' => 'legal.notice',
            'titles' => ['it' => 'Note legali', 'en' => 'Legal notice'],
            'descriptions' => [
                'it' => 'Informazioni societarie e contatti ufficiali di WAYOUT.',
                'en' => 'Company information, liability and legal references for Wayout.',
            ],
        ],
        'marketing' => [
            'group' => 'consent_texts',
            'initial_version' => '1.0',
            'route' => 'legal.privacy',
            'titles' => ['it' => 'Consenso marketing', 'en' => 'Marketing consent'],
            'descriptions' => [
                'it' => 'Testo del consenso facoltativo mostrato nel modulo waitlist.',
                'en' => 'Optional consent wording displayed in the waitlist form.',
            ],
            'initial_content' => [
                'it' => '<p>Acconsento a ricevere comunicazioni di marketing sul lancio di WAYOUT e promozioni esclusive (facoltativo).</p>',
                'en' => '<p>I agree to receive marketing communications about the WAYOUT launch and exclusive promotions (optional).</p>',
            ],
        ],
        'waitlist_acceptance' => [
            'group' => 'consent_texts',
            'initial_version' => '1.0',
            'route' => 'legal.privacy',
            'titles' => ['it' => 'Informativa iscrizione waitlist', 'en' => 'Waitlist registration notice'],
            'descriptions' => [
                'it' => 'Testo mostrato prima del completamento della registrazione alla waitlist.',
                'en' => 'Wording displayed before completing the waitlist registration.',
            ],
            'initial_content' => [
                'it' => '<p>Completando l’iscrizione alla waitlist dichiari di aver letto la <a href="/privacy-policy" target="_blank">Privacy policy</a> e i <a href="/termini-e-condizioni" target="_blank">Termini sito e waitlist</a>.</p>',
                'en' => '<p>By completing your waitlist registration, you confirm that you have read the <a href="/privacy-policy" target="_blank">Privacy policy</a> and the <a href="/termini-e-condizioni" target="_blank">Website and waitlist terms</a>.</p>',
            ],
        ],
        'contact_acceptance' => [
            'group' => 'consent_texts',
            'initial_version' => '1.0',
            'route' => 'legal.privacy',
            'titles' => ['it' => 'Dichiarazione form contatti', 'en' => 'Contact form acknowledgement'],
            'descriptions' => [
                'it' => 'Testo obbligatorio mostrato nella checkbox del form contatti.',
                'en' => 'Required wording displayed in the contact form checkbox.',
            ],
            'initial_content' => [
                'it' => '<p>Dichiaro di aver letto la <a href="/privacy-policy" target="_blank">Privacy policy</a> e i <a href="/termini-e-condizioni" target="_blank">Termini e condizioni</a>.</p>',
                'en' => '<p>I confirm that I have read the <a href="/privacy-policy" target="_blank">Privacy policy</a> and the <a href="/termini-e-condizioni" target="_blank">Terms and conditions</a>.</p>',
            ],
        ],
        'purchase_acceptance' => [
            'group' => 'consent_texts',
            'initial_version' => '1.0',
            'route' => 'legal.sales',
            'titles' => ['it' => 'Dichiarazione acquisto', 'en' => 'Purchase acknowledgement'],
            'descriptions' => [
                'it' => 'Testo obbligatorio mostrato nella checkbox prima del pagamento.',
                'en' => 'Required wording displayed in the checkbox before payment.',
            ],
            'initial_content' => [
                'it' => '<p>Confermi di aver letto i <a href="/termini-di-vendita" target="_blank">Termini di vendita</a>, le <a href="/condizioni-di-pre-sale" target="_blank">Condizioni di pre-sale</a> e la pagina <a href="/recedere-dal-contratto" target="_blank">Recesso e rimborsi</a>.</p>',
                'en' => '<p>You confirm that you have read the <a href="/termini-di-vendita" target="_blank">Sales terms</a>, the <a href="/condizioni-di-pre-sale" target="_blank">Pre-sale terms</a> and the <a href="/recedere-dal-contratto" target="_blank">Withdrawal and refunds</a> page.</p>',
            ],
        ],
    ],
];
