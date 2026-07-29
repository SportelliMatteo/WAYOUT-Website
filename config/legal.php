<?php

return [
    /*
     * Questi valori servono solo per la prima importazione nel database.
     * Dopo l'importazione, testi e versioni correnti sono gestiti dalla dashboard admin.
     */
    'documents' => [
        'privacy' => [
            'group' => 'policies',
            'initial_version' => '1.2',
            'view' => 'pages/legal/privacy.blade.php',
            'route' => 'legal.privacy',
            'titles' => ['it' => 'Privacy policy', 'en' => 'Privacy policy'],
            'descriptions' => [
                'it' => 'Informativa sul trattamento dei dati personali degli utenti del sito WAYOUT.',
                'en' => 'Information on the processing of personal data of Wayout website users.',
            ],
        ],
        'cookies' => [
            'group' => 'policies',
            'initial_version' => '1.1',
            'view' => 'pages/legal/cookies.blade.php',
            'route' => 'legal.cookies',
            'titles' => ['it' => 'Cookie policy', 'en' => 'Cookie policy'],
            'descriptions' => [
                'it' => 'Informativa sull’uso di cookie e strumenti simili sul sito WAYOUT.',
                'en' => 'Information about cookies and similar technologies used on the Wayout website.',
            ],
        ],
        'terms' => [
            'group' => 'policies',
            'initial_version' => '2026-07-29',
            'view' => 'pages/legal/terms.blade.php',
            'route' => 'legal.terms',
            'titles' => ['it' => 'Termini sito e waitlist', 'en' => 'Website and waitlist terms'],
            'descriptions' => [
                'it' => 'Termini di utilizzo del sito WAYOUT e della waitlist pre-lancio.',
                'en' => 'Terms for using the WAYOUT website and pre-launch waitlist.',
            ],
        ],
        'passes' => [
            'group' => 'commerce',
            'initial_version' => '2026-07-29.1',
            'view' => 'pages/legal/passes.blade.php',
            'route' => 'legal.passes',
            'titles' => ['it' => 'Come funzionano i Pass', 'en' => 'How Passes work'],
            'descriptions' => [
                'it' => 'Condizioni, decorrenza e limitazioni dei Waitlist Pass e Founder Pass WAYOUT.',
                'en' => 'Conditions, start date and limitations of WAYOUT Waitlist and Founder Passes.',
            ],
        ],
        'sales' => [
            'group' => 'commerce',
            'initial_version' => '2026-07-29.1',
            'view' => 'pages/legal/sales.blade.php',
            'route' => 'legal.sales',
            'titles' => ['it' => 'Termini di vendita', 'en' => 'Terms of sale'],
            'descriptions' => [
                'it' => 'Termini per l’acquisto online dei Founder Pass WAYOUT.',
                'en' => 'Terms for purchasing WAYOUT Founder Passes online.',
            ],
        ],
        'presale' => [
            'group' => 'commerce',
            'initial_version' => '2026-07-29.1',
            'view' => 'pages/legal/presale.blade.php',
            'route' => 'legal.presale',
            'titles' => ['it' => 'Condizioni di pre-sale', 'en' => 'Pre-sale terms'],
            'descriptions' => [
                'it' => 'Condizioni specifiche della pre-sale dei Founder Pass WAYOUT.',
                'en' => 'Specific terms for the pre-sale of WAYOUT Founder Passes.',
            ],
        ],
        'refunds' => [
            'group' => 'commerce',
            'initial_version' => '2026-07-29.2',
            'view' => 'pages/legal/refunds.blade.php',
            'route' => 'legal.refunds',
            'titles' => ['it' => 'Refund Policy', 'en' => 'Refund Policy'],
            'descriptions' => [
                'it' => 'Regole sui rimborsi legali e contrattuali degli acquisti WAYOUT.',
                'en' => 'Rules on legal and contractual refunds for WAYOUT purchases.',
            ],
        ],
        'withdrawal_info' => [
            'group' => 'commerce',
            'initial_version' => '2026-07-29.2',
            'view' => 'pages/legal/withdrawal-info.blade.php',
            'route' => 'legal.refunds',
            'titles' => ['it' => 'Informativa sul diritto di recesso', 'en' => 'Withdrawal right information'],
            'descriptions' => [
                'it' => 'Informativa sul diritto di recesso per gli acquisti WAYOUT.',
                'en' => 'Information about withdrawal rights for WAYOUT purchases.',
            ],
        ],
        'notice' => [
            'group' => 'policies',
            'initial_version' => '2026-08-01.1',
            'view' => 'pages/legal/notice.blade.php',
            'route' => 'legal.notice',
            'titles' => ['it' => 'Note legali', 'en' => 'Legal notice'],
            'descriptions' => [
                'it' => 'Informazioni societarie, responsabilità e riferimenti legali di Wayout.',
                'en' => 'Company information, liability and legal references for Wayout.',
            ],
        ],
        'marketing' => [
            'group' => 'consent_texts',
            'initial_version' => '2026-07-14',
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
            'initial_version' => '2026-07-29',
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
            'initial_version' => '2026-07-14',
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
            'initial_version' => '2026-07-29.2',
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
