@extends('pages.legal.layout', [
    'title' => 'Privacy policy',
    'description' => 'Informativa sul trattamento dei dati personali degli utenti del sito WAYOUT.',
])

@section('legal-content')
<div class="space-y-8">
    <section>
        <h2 class="text-2xl font-black text-slate-950">Scope of the document</h2>
        <p class="mt-3">This information covers the wayoutapp.it website, waitlist, pre-sale of Founder Pass, checkout, contact forms and related communications. The WAYOUT App does not regulate treatments after launch, which will be described in a specific privacy policy of the app.</p>
    </section>

    <section>
        <h2 class="text-2xl font-black text-slate-950">1. Subject and scope of application</h2>
        <p class="mt-3">This Privacy Policy describes how WAYOUT S.r.l. processes the personal data of people who visit the wayoutapp.it website, join the waitlist, verify their email address, access the pre-sale, create or connect a WAYOUT account during the purchase flow, buy a Founder Pass, request an invoice, contact WAYOUT for support, exercise their right of withdrawal, request a refund or exercise their personal-data protection rights.</p>
        <p class="mt-3">The information applies exclusively to the processing carried out within the site and the pre-launch flows mentioned above. The sites, services and platforms of third parties that may be reached via links, including social networks and the Stripe payment environment, apply their privacy statements for treatments performed according to their respective roles. The simple connection to an external service does not mean by itself the activation of tracking tools on the WAYOUT site.</p>
    </section>

    <section>
        <h2 class="text-2xl font-black text-slate-950">2. Data controller and contacts</h2>
        <p class="mt-3">The controller is:</p>
        <ul class="mt-3 list-disc space-y-2 pl-6">
            <li>WAYOUT S.r.l.;</li>
            <li>Tax code and VAT: 14805930964;</li>
            <li>registered office: Via Guglielmo Marconi 24/B, 20082 Binasco (MI), Italy;</li>
            <li>PEC: <a class="font-black text-violet-700" href="mailto:wayout@pec.wayoutapp.it">wayout@pec.wayoutapp.it</a>;</li>
            <li>email for privacy and data protection requests: <a class="font-black text-violet-700" href="mailto:amministrazione@wayoutapp.it">amministrazione@wayoutapp.it</a>;</li>
            <li>email for general assistance: <a class="font-black text-violet-700" href="mailto:amministrazione@wayoutapp.it">amministrazione@wayoutapp.it</a>.</li>
        </ul>
        <p class="mt-3">Requests for the exercise of privacy rights must be sent to <a class="font-black text-violet-700" href="mailto:amministrazione@wayoutapp.it">amministrazione@wayoutapp.it</a>, indicating in the subject “Privacy request”.</p>
    </section>

    <section>
        <h2 class="text-2xl font-black text-slate-950">3. Categories and origin of processed data</h2>
        <h3 class="mt-6 text-xl font-black text-slate-900">3. Data provided directly by the user</h3>
        <ul class="mt-3 list-disc space-y-2 pl-6">
            <li>waitlist data: email address and choice regarding marketing consent; waitlist does not require name, surname, date of birth or telephone number;</li>
            <li>choice regarding marketing consent, which is optional and separated from the registration to the waitlist;</li>
            <li>checkout, account and purchase data: Founder Pass selected, first name, last name, date of birth, gender, verified phone number, WAYOUT account identifier and automatically generated nickname; if invoice is required, also type of customer, tax code or social reason and VAT number, address, postcode, municipality, Province, State, PEC and recipient code SDI where applicable;</li>
            <li>data inserted in the contact form: name, email, subject and content of the message;</li>
            <li>data and communications provided for assistance, complaints, withdrawal, refunds or privacy requests, including the order number and information necessary to manage the request.</li>
        </ul>
        <h3 class="mt-6 text-xl font-black text-slate-900">3.2 Data generated during use of the site</h3>
        <ul class="mt-3 list-disc space-y-2 pl-6">
            <li>date and time of request and email verification, internal identifiers, verification tokens stored in hash form until expiry/use, waitlist position, offer status and information necessary to manage the limit of available places;</li>
            <li>selected plan, promotional package code, amount, currency, order status or payment, number or internal identification of the purchase, WAYOUT account identifier, Stripe session identifier and technical data required to verify the entity;</li>
            <li>notices relating to the information, consents and accepted conditions, such as date, time, source, version/hash of documents, IP address, user agent and session identifier in hash form, within the limits necessary to document the choice and prevent abuse;</li>
            <li>Technical data of navigation and security, such as IP address, user agent, browser information, session identifiers, CSRF tokens, language preference, application logs, anti-spam events and abnormal access or use attempts.</li>
            <li>data collected, after consent and for the respective category, through Google Tag Manager, Google Analytics 4 and Meta Pixel, such as online identifiers and cookies, device and browser information, IP address, approximate geographical area, page or content displayed, source of origin, events, conversions and interactions with the site and with advertising campaigns.</li>
        </ul>
        <h3 class="mt-6 text-xl font-black text-slate-900">3.3 Data received by third parties</h3>
        <p class="mt-3">To protect public contact, waitlist, offer access, confirmation resend, withdrawal and administrative forms from spam and abuse, WAYOUT uses Google reCAPTCHA v3. The service generates a technical token and risk score that the site verifies on the server and may process technical information such as the IP address, browser characteristics and request data. In relation to identity and checkout, WAYOUT also uses Firebase Phone Authentication and its reCAPTCHA to verify the phone number and obtain the technical token needed to create or recognize the account on the WAYOUT backend. The Firebase token is processed temporarily by the site and forwarded to the backend; the verified number is stored in the account profile. In relation to payments, WAYOUT receives from the WAYOUT backend and Stripe the information necessary to reconcile the order, including outcome, amount, currency and session/transaction identifiers. Full card data and CVC are not acquired or stored directly by WAYOUT.</p>
        <p class="mt-3">In relation to sending communications, WAYOUT may receive technical information about sending, delivery, rebounds and disiscrimination from Brevo. At the date of this version, the individual tracking of openings and clicks by pixels or equivalent tools is disabled. For electronic billing, Qonto can return customer/invoice identifiers, invoice status and transmission status, SdI events, technical errors and courtesy PDFs. In relation to measurement and online advertising, Google and Meta can return statistics, events, segments and aggregated or pseudonymized information exclusively after relevant consent.</p>
        <p class="mt-3">The WAYOUT backend, connected to the site via signed server-to-server API, receives the data strictly necessary to create/recognize the account, verify the suitability, generate Stripe checkout and confirm the entity. The site retains the necessary references to reconcile accounts, order and pre-launch benefit.</p>
        <h3 class="mt-6 text-xl font-black text-slate-900">3.4 Special data and free content</h3>
        <p class="mt-3">WAYOUT does not require, through the site, particular categories of personal data pursuant to Article 9 of the GDPR, such as health, biometric, religious, political or sexual life related data. It is therefore called upon not to include such information in the fields free of charge, unless it is strictly necessary for a specific request and is a legal basis.</p>
    </section>

    <section>
        <h2 class="text-2xl font-black text-slate-950">4. Purposes, legal bases and retention times</h2>
        <p class="mt-3">The data are processed for the purposes and according to the legal bases indicated below. The retention periods may be extended when necessary to comply with legal obligations, to respond to requests from authorities, to ascertain, exercise or defend a right, or to manage an accident or a concrete dispute.</p>
        <h3 class="mt-6 text-xl font-black text-slate-900">4.1 Technical operation of the site and security</h3>
        <p class="mt-3"><strong>Data processed: </strong>Navigation data, session, CSRF tokens, language preference, IP, user agent, technical logs and anti-spam or security events.</p>
        <p class="mt-3"><strong>Purpose: </strong>Enable form and session functioning, maintain access to pre-sale flow, prevent abuse and fraud, protect the site, diagnose abnormalities and ensure operational continuity.</p>
        <p class="mt-3"><strong>Legal basis: </strong>The legitimate interest of WAYOUT to the safety and proper functioning of the service, as well as to fulfil the applicable security obligations (Article 6(1)(c) and f), GDPR).</p>
        <p class="mt-3"><strong>Retention: </strong>Session data are stored for the configured technical duration. Ordinary technical logs are kept, as a rule, for 30 days. Security and administrative logs can be stored up to 12 months; for accesses of any system administrators is ensured a storage not less than 6 months, where applicable. Data can be kept longer when this is necessary to handle accidents, abuses, litigations or requests of authorities.</p>
        <p class="mt-3"><strong>Provision of data: </strong>The strictly necessary technical data are collected automatically; their lack of treatment can prevent the proper functioning of the site.</p>
        <h3 class="mt-6 text-xl font-black text-slate-900">4.2 Registration and management of waitlist</h3>
        <p class="mt-3"><strong>Data processed: </strong>Email, timestamp, identification and status of registration, token verification in hash form, date/time verification, waitlist position, offer status and marketing consent if expressed.</p>
        <p class="mt-3"><strong>Purpose: </strong>Register the request, verify control of the email address through a magic link, assign a waitlist position only after verification, manage the valid-user limit and enable the allocation of the Waitlist Pass under the applicable conditions.</p>
        <p class="mt-3"><strong>Legal basis: </strong>Execution of pre-contractual measures taken at the request of the data subject and legitimate interest of WAYOUT to organize and protect the pre-launch phase (Art. 6, par. 1, letter b) and f), GDPR).</p>
        <p class="mt-3"><strong>Retention: </strong>Up to the public go-live of the app and for the next 12 months. After this period, the data is deleted or anonymized if the user has not created an account in the app and has not made a purchase, unless a different legal basis, legal obligations or rights protection requirements.</p>
        <p class="mt-3"><strong>Provision of data: </strong>The email address and acceptance of the Waitlist Terms are necessary to complete registration. Marketing consent is optional and does not affect the waitlist position or access to offers.</p>
        <h3 class="mt-6 text-xl font-black text-slate-900">4.3 Verification of age, phone and account creation/recognition</h3>
        <p class="mt-3"><strong>Data processed: </strong>Name, surname, date of birth and outcome 18+, gender, prefix and phone number, OTP/token Firebase treated temporarily, account identifier and nickname generated automatically.</p>
        <p class="mt-3"><strong>Purpose: </strong>Check that the buyer is older, check the phone number, prevent abuse and create or recognize the WAYOUT account needed to connect the Founder Pass. These operations take place in checkout and not in the waitlist form.</p>
        <p class="mt-3"><strong>Legal basis: </strong>Execution of pre-contractual and legitimate interest measures by WAYOUT to ensure the safety and compliance of the service (Article 6(1)(b) and f), GDPR).</p>
        <p class="mt-3"><strong>Retention: </strong>Firebase technical tokens are treated for the time strictly necessary for verification. Your account details are stored according to your relationship; if the account is created during a checkout then abandoned and is not used for other services, cancellation or anonymization is expected within 30 days of the last attempt, except security, disputes or other legal basis. Data linked to an order concluded follow the applicable contractual/fiscal retention.</p>
        <p class="mt-3"><strong>Provision of data: </strong>Providing this data is mandatory to access flows reserved for adults.</p>
        <h3 class="mt-6 text-xl font-black text-slate-900">4.4 Service communications related to waitlist and launch</h3>
        <p class="mt-3"><strong>Data processed: </strong>Email and information concerning registration verification, waitlist position and eligibility for the Waitlist Pass.</p>
        <p class="mt-3"><strong>Purpose: </strong>Send registration confirmations, updates strictly necessary on the availability of the service, instructions to complete the registration to the go-live and operational information on the Waitlist Pass.</p>
        <p class="mt-3"><strong>Legal basis: </strong>execution of pre-contractual measures or of the service requested by the user (art. 6, par. 1, letter b), GDPR).</p>
        <p class="mt-3"><strong>Retention: </strong>For the duration of the waitlist and for the time required to complete any activation of the benefit; afterwards, for the periods applicable to the profile or contractual relationship.</p>
        <p class="mt-3"><strong>Provision of data: </strong>Registration involves receiving only communications strictly necessary for the management of the waitlist and the benefit required.</p>
        <h3 class="mt-6 text-xl font-black text-slate-900">4.5 Marketing, newsletters and promotional communications</h3>
        <p class="mt-3"><strong>Data processed: </strong>Email, marketing consent and relevant evidence; technical data relating to sending, delivery, rebound and unsubscribing of communications managed through Brevo. The individual tracking of openings and clicks is disabled at the date of this version.</p>
        <p class="mt-3"><strong>Purpose: </strong>Send offers, invitations to pre-sale, promotions, commercial news and initiatives of WAYOUT.</p>
        <p class="mt-3"><strong>Legal basis: </strong>Free consent, specific, informed and revoked (art. 6, par. 1, lit. a), and art. 7 GDPR; art. 130 of Legislative Decree 196/2003).</p>
        <p class="mt-3"><strong>Retention: </strong>Until the withdrawal of consent and, however, for a maximum of 24 months from the last active and documentable interaction of the user. Proof of consent and revocation may be retained for further 5 years for purposes of accountability and defence. The technical sending logs are kept according to the configuration of the provider and the Data Retention Policy of WAYOUT, within the necessary limits.</p>
        <p class="mt-3"><strong>Provision of data: </strong>The consent is optional, it is not pre-selected and the lack of consent does not prevent the entry to waitlist or purchase.</p>
        <h3 class="mt-6 text-xl font-black text-slate-900">4.6 Access to pre-sale, checkout and purchase attempts</h3>
        <p class="mt-3"><strong>Data processed: </strong>Email, personal and contact data, selected plan, amount, currency, checkout status, timestamp, internal identification and technical data necessary for the temporary booking of the pass.</p>
        <p class="mt-3"><strong>Purpose: </strong>Show the pre-sale offer, check requirements, temporarily reserve availability, pre-checkout, manage unfinished errors and attempts.</p>
        <p class="mt-3"><strong>Legal basis: </strong>Execution of pre-contractual measures at your request and legitimate interest of WAYOUT to properly manage availability and prevent abuse (Art. 6, par. 1, letter b) and f), GDPR).</p>
        <p class="mt-3"><strong>Retention: </strong>Data relating to uncompleted, failed or expired attempts are kept, as a rule, for a maximum of 30 days from abandonment or last attempt, except for technical, anti-fraud or protection requirements.</p>
        <p class="mt-3"><strong>Provision of data: </strong>The data required at checkout are mandatory to proceed to purchase.</p>
        <h3 class="mt-6 text-xl font-black text-slate-900">4.7 Purchase and management of Founder Pass</h3>
        <p class="mt-3"><strong>Data processed: </strong>Personal and contact data, selected pass, price, currency, order number, date of purchase, order status and payment, Stripe identifiers, contractual communications and activation data.</p>
        <p class="mt-3"><strong>Purpose: </strong>End and execute the sales contract, register and confirm the order, associate and activate the pass, provide assistance and manage contractual communications.</p>
        <p class="mt-3"><strong>Legal basis: </strong>Execution of the contract and fulfilment of legal, tax and accounting obligations (Article 6, paragraph 1, letter b) and c), GDPR), as well as legitimate interest in the protection of rights (lett. f).</p>
        <p class="mt-3"><strong>Retention: </strong>As a rule, 10 years after the conclusion of the operation or the relationship, or for the different period provided for by the applicable legislation and the requirements of contractual protection.</p>
        <p class="mt-3"><strong>Provision of data: </strong>The provision of the required data is necessary to purchase and manage the Founder Pass.</p>
        <h3 class="mt-6 text-xl font-black text-slate-900">4.8 Payment via Stripe</h3>
        <p class="mt-3"><strong>Data processed: </strong>Order data, email, amount, currency, identification and transaction status. The complete data of the paper, CVC and further anti-fraud data are processed directly by Stripe.</p>
        <p class="mt-3"><strong>Purpose: </strong>Execute payment, verify outcome, prevent fraud, reconcile order and manage refunds or disputes.</p>
        <p class="mt-3"><strong>Legal basis: </strong>Execution of the contract, legal obligations and legitimate interest in the prevention of fraud and the protection of rights (Article 6, paragraph 1, letter b), c) and f), GDPR). Stripe processes data according to the roles and legal bases described in its statement.</p>
        <p class="mt-3"><strong>Retention: </strong>WAYOUT retains transaction references for the period applicable to the order, as a rule 10 years. Stripe applies its retention times.</p>
        <p class="mt-3"><strong>Provision of data: </strong>The processing of payment data is necessary to complete the purchase.</p>
        <h3 class="mt-6 text-xl font-black text-slate-900">4.9 Invoice and accounting requirements</h3>
        <p class="mt-3"><strong>Data processed: </strong>Name, surname, order data and, only if you require invoice, tax code, address, postcode, municipality, province, state, any billing email and other data strictly necessary for the issue and retention of the tax document.</p>
        <p class="mt-3"><strong>Purpose: </strong>Enter the required invoice, register the transaction, manage refunds and fulfill tax and accounting obligations.</p>
        <p class="mt-3"><strong>Legal basis: </strong>Compliance with legal obligations and execution of the contract (Art. 6, par. 1, lit. c) and b), GDPR).</p>
        <p class="mt-3"><strong>Retention: </strong>As a rule, 10 years, or for any different period required by applicable tax, accounting and civil-law provisions.</p>
        <p class="mt-3"><strong>Provision of data: </strong>The invoice request is optional; the tax code becomes mandatory only when the invoice is requested.</p>
        <h3 class="mt-6 text-xl font-black text-slate-900">4.10 Contacts, support and information requests</h3>
        <p class="mt-3"><strong>Data processed: </strong>Name, email, object, message and more information voluntarily provided.</p>
        <p class="mt-3"><strong>Purpose: </strong>Respond to requests from users, creators, locals, partners or others interested in maintaining the necessary history of contact management.</p>
        <p class="mt-3"><strong>Legal basis: </strong>Execution of pre-contractual measures at the request of the data subject or legitimate interest of WAYOUT to manage communications and relationships with users and partners (Art. 6, par. 1, letter b) and f), GDPR).</p>
        <p class="mt-3"><strong>Retention: </strong>Up to 24 months from closing the request. If a contract, a complaint or a dispute arises from the contact, the relevant period shall apply.</p>
        <p class="mt-3"><strong>Provision of data: </strong>The mandatory fields of the form are necessary to receive and manage the request.</p>
        <h3 class="mt-6 text-xl font-black text-slate-900">4.11 Refunds, complaints and complaints</h3>
        <p class="mt-3"><strong>Data processed: </strong>Identity, email, order number and data, request, communications, motivation provided, outcome, amount and reference of the refund.</p>
        <p class="mt-3"><strong>Purpose: </strong>Manage the right of withdrawal, refund requests, complaints, disputes and related documentation.</p>
        <p class="mt-3"><strong>Legal basis: </strong>Execution of the contract, fulfilment of legal obligations and legitimate interest in the management and defence of rights (Article 6, paragraph 1, letter b), c) and f), GDPR).</p>
        <p class="mt-3"><strong>Retention: </strong>10 years when the request is linked to an order or payment; in other cases, as a rule, 5 years after closing, subject to litigation or further obligations.</p>
        <p class="mt-3"><strong>Provision of data: </strong>The necessary data must be provided to allow the identification of the order and the evaluation of the request.</p>
        <h3 class="mt-6 text-xl font-black text-slate-900">4.12 Management of privacy requests</h3>
        <p class="mt-3"><strong>Data processed: </strong>Identification and contact data, content of the request, any elements necessary to verify the identity, response and documentation of the activities carried out.</p>
        <p class="mt-3"><strong>Purpose: </strong>Receiving, verifying and responding to the claims for exercise of the rights provided by the GDPR and documenting compliance.</p>
        <p class="mt-3"><strong>Legal basis: </strong>Compliance with a legal obligation (Art. 6, par. 1, lit. c), GDPR).</p>
        <p class="mt-3"><strong>Retention: </strong>As a rule 5 years after closing the request, unless longer storage is required for disputes or requests of the authorities.</p>
        <p class="mt-3"><strong>Provision of data: </strong>WAYOUT may request reasonable information to verify the identity of the applicant.</p>
        <h3 class="mt-6 text-xl font-black text-slate-900">4.13 Proof of consent, information and accepted conditions</h3>
        <p class="mt-3"><strong>Data processed: </strong>User identifier or order, date and time, source, text version, choice made and, where proportional, IP address.</p>
        <p class="mt-3"><strong>Purpose: </strong>Demonstrate compliance with transparency obligations, the validity of marketing consent and applicable contractual conditions.</p>
        <p class="mt-3"><strong>Legal basis: </strong>Consent, execution of the contract, legal obligations and legitimate probatory interest (art. 6, par. 1, letter a), b), c) and f), GDPR, according to the individual case).</p>
        <p class="mt-3"><strong>Retention: </strong>log marketing is kept for the duration of consent and for 5 years from revocation; the evidence for sale is kept by order, as a rule for 10 years; the indications related to the waitlist, as a rule, for the duration of the report and 5 years later.</p>
        <p class="mt-3"><strong>Provision of data: </strong>Registration of the view or acceptance is necessary when required to access the relevant service or conclude the contract.</p>
        <h3 class="mt-6 text-xl font-black text-slate-900">4.14 Statistical analysis, measurement and online advertising</h3>
        <p class="mt-3"><strong>Data processed: </strong>Online identifiers and cookies, IP address, browser and device data, approximate geographical area, URL and page displayed, referrer, navigation events, content interactions, conversions and campaign data. The tools provided are Google Tag Manager, Google Analytics 4 and Meta Pixel in the browser and, when configured, the server-side Conversions API.</p>
        <p class="mt-3"><strong>Purpose: </strong>Measure the use of the site and the effectiveness of the campaigns, produce statistics, identify navigation problems, optimize content and streams of waitlist/pre-sale and, through Meta Pixel, measure campaigns, create public or carry out remarketing, where activated.</p>
        <p class="mt-3"><strong>Legal basis: </strong>Preventive consent of the user (art. 6, par. 1, lit. a), GDPR and art. 122 of Legislative Decree 196/2003). Google Tag Manager and Google Analytics 4 are blocked until consent to the Analytics category; Meta Pixel is blocked until consent to the Marketing category. The initial configuration uses Google Allow Mode in Basic mode and does not require sending Google or Meta tracking signals before the relevant consent.</p>
        <p class="mt-3"><strong>Retention: </strong>User-level data and event in Google Analytics 4 are configured for 14 months retention, except for aggregate data without personal identification. The data and identifiers managed by Meta are stored according to the account settings, the durations indicated in the Cookie Policy and the rules of the provider. The cookie choice is kept, as a rule, for 6 months, unless prior modification of preferences or need to document consent.</p>
        <p class="mt-3"><strong>Provision of data: </strong>The consent is optional and distinct for the categories Analytics and Marketing. In case of refusal, the site and the essential services remain usable and the relevant unnecessary tools are not activated.</p>
    </section>

    <section>
        <h2 class="text-2xl font-black text-slate-950">5. Compulsory or optional nature of the award</h2>
        <p class="mt-3">Fields marked as mandatory are necessary to provide the required service. Failure to provide may prevent waitlist registration, verification of 18+ requirement, access to pre-sale, purchase completion or request management.</p>
        <ul class="mt-3 list-disc space-y-2 pl-6">
            <li>Marketing consent is always optional and separate. Its failure to perform does not result in access to the waitlist or the possibility to purchase.</li>
            <li>The invoice request is optional. The tax code is only required if you select the option to receive the invoice.</li>
            <li>The technical data strictly necessary for the operation and safety of the site are collected automatically.</li>
            <li>Consent to Analytics and Marketing tools is optional and can be provided separately. The refusal does not prevent access to the site, the waitlist or the pre-sale; prevents only the activation of unnecessary tags.</li>
        </ul>
    </section>

    <section>
        <h2 class="text-2xl font-black text-slate-950">6. Service Communications and Marketing Communications</h2>
        <p class="mt-3">The communications necessary to confirm the registration, manage the waitlist, inform about the actual availability of the service, activate the Waitlist Pass, confirm or manage a purchase, communicate delays, contractual changes, withdrawal or refunds are service or contractual communications and may also be sent in the absence of marketing consent, as long as they are strictly connected to the request or relationship with the user.</p>
        <p class="mt-3">Communications that promote offers, Founder Pass, commercial advantages, initiatives or other promotional activities are sent only to users who have expressed a specific marketing consent. The consent can be revoked at any time by means of the unsubscribe link in the communications, when available, or by writing to <a class="font-black text-violet-700" href="mailto:amministrazione@wayoutapp.it">amministrazione@wayoutapp.it</a>. The revocation shall not prejudice the lawfulness of the processing carried out before the revocation and shall not interrupt the communications strictly necessary for the management of the waitlist or the contract.</p>
        <p class="mt-3">On the date of this version, WAYOUT does not use tracking pixels or equivalent features to detect opening or clicking in transactional emails or marketing. Any future activation of such instruments will be preceded by updating the information and, when required by applicable legislation, by collecting a specific consent.</p>
    </section>

    <section>
        <h2 class="text-2xl font-black text-slate-950">7. Data controllers</h2>
        <p class="mt-3">The data can be communicated, within the necessary limits, to the following categories of subjects:</p>
        <ul class="mt-3 list-disc space-y-2 pl-6">
            <li>staff, administrators and collaborators authorized by WAYOUT, in compliance with specific instructions and the principle of necessity;</li>
            <li>Aruba and any subjects involved in hosting, infrastructure, database, backups, logs and site security. The hosting infrastructure chosen by WAYOUT is located within the European Union;</li>
            <li>development, maintenance, technical assistance and cybersecurity providers;</li>
            <li>Brevo, used for sending and managing transactional emails and marketing communications, including delivery, rebounds and disiscrimination. The individual tracking of openings and clicks is disabled at the date of this version;</li>
            <li>Google, for Google Tag Manager and Google Analytics 4, and Meta Platforms, for Meta Pixel and Conversions API, only after relevant consent and according to their privacy roles;</li>
            <li>Stripe and its financial and technical partners for payment processing, fraud prevention, reconciliation and refunds;</li>
            <li>accountant, tax consultants, billing systems and other subjects necessary for administrative and accounting purposes;</li>
            <li>Legal advisers, privacy, insurance or security when necessary to protect WAYOUT or users;</li>
            <li>judicial, administrative, tax or public security authorities and other subjects to which the communication is mandatory by law or necessary to ascertain, exercise or defend a right.</li>
        </ul>
        <p class="mt-3">Data is not widespread. Suppliers who process data on behalf of WAYOUT are appointed processors under Article 28 of the GDPR, where required; other subjects, such as Stripe for specific activities, may also operate as independent owners.</p>
    </section>

    <section>
        <h2 class="text-2xl font-black text-slate-950">8. Payments via Stripe</h2>
        <p class="mt-3">Founder Pass checkout is managed via Stripe. When the user initiates the payment, it interacts with an environment and with technical tools provided by Stripe. Stripe can collect and process payment data, transaction, device, network and fraud prevention, and, depending on the activity, can act as responsible for processing on behalf of WAYOUT and/or as independent owner.</p>
        <p class="mt-3">WAYOUT receives and retains only the information necessary to manage the order, such as the outcome, amount, currency, session identifier or transaction and reconciliation data. WAYOUT does not receive or retain the full card number or CVC code. To learn more about the treatments carried out by Stripe, you are invited to consult the privacy policy of Stripe available on the website.</p>
        <h3 class="mt-6 text-xl font-black text-slate-900">8.1 Electronic billing via Qonto.</h3>
        <p class="mt-3">If you require invoice, WAYOUT sends Qonto the tax data necessary to create the customer and document, register it as paid and, in production, manage electronic transmission and the related states. WAYOUT retains the necessary identifications and events for tax, accounting, assistance and reconciliation obligations for the period prescribed by law.</p>
    </section>

    <section>
        <h2 class="text-2xl font-black text-slate-950">9. Cookies, technical tools, analytics and advertising</h2>
        <p class="mt-3">The site uses cookies and technical tools necessary for the operation of the Laravel application, session management, CSRF protection, storage of language preference and registration of cookie choices. Google reCAPTCHA v3 protects sensitive public forms; in checkout, Firebase Phone Authentication and its reCAPTCHA may also use technical identifiers, network requests and storage strictly necessary for phone verification and abuse prevention. Stripe payment tools are used in checkout managed by the WAYOUT backend. Strictly necessary tools are used only for the requested function and not for advertising purposes, without prejudice to the transparency obligation.</p>
        <h3 class="mt-6 text-xl font-black text-slate-900">9.1 Google Analytics 4</h3>
        <p class="mt-3">After consent Analytics, WAYOUT uses Google Analytics 4 to obtain statistics on site usage, visited pages, traffic sources, events and conversions. Google Analytics may treat cookies, online identifiers and information on browsers, devices, IP address, approximate geographical area and interactions. The initial configuration includes Google Signals, advertising customization, User-ID, cross-domain tracking and Enhanced Conversions disabled. WAYOUT does not send Google emails, telephone number, tax code or other directly identifiable data.</p>
        <h3 class="mt-6 text-xl font-black text-slate-900">9.2 Google Tag Manager</h3>
        <p class="mt-3">Google Tag Manager is used to manage and distribute site tags and not as a user database. The container and Google Analytics 4 are loaded only after consent Analytics, according to Google Allow Mode in Basic mode. Tags belonging to other categories, including Meta Pixel, can only be activated after specific consent to the relevant category.</p>
        <h3 class="mt-6 text-xl font-black text-slate-900">9.3 Meta Pixel</h3>
        <p class="mt-3">After Marketing consent, WAYOUT uses Meta Pixel in the browser and, when configured, the server-side Conversions API to measure campaign visits and conversions, understand the effectiveness of advertising communication and, where enabled, create customized publics or perform remarketing. Meta can receive online identifiers, cookies, IP address, browser and device information, page visited, referrer and events on the site. Automatic Pixel Advanced Matching remains disabled. When configured, the Conversions API requires Marketing consent.</p>
        <h3 class="mt-6 text-xl font-black text-slate-900">9.4 Choice and revocation of consent</h3>
        <p class="mt-3">Google Analytics 4 and Meta Pixel are not required to use the site. You can accept, reject or change the Analytics and Marketing categories separately through the banner and cookie preferences panel. The revocation shall have effect for the future and shall not prejudice the lawfulness of the processing carried out before the revocation. The refusal does not prevent the entry to waitlist, access to pre-sale or purchase.</p>
        <p class="mt-3">For the updated list of cookies, the respective durations, suppliers and management methods refer to the Cookie Policy published on the site.</p>
    </section>

    <section>
        <h2 class="text-2xl font-black text-slate-950">10. Data transfer outside the European Economic Area</h2>
        <p class="mt-3">The main hosting of the site uses Aruba with MySQL database. Brevo is used for email communications; Google/Firebase for telephone verification, reCAPTCHA and, after consent, analytics; Meta, after consent, for marketing measurement; Stripe for payments and Qonto for electronic billing. Some of these suppliers or subcontractors may process or make data accessible even outside the European Economic Area according to their contractual arrangement.</p>
        <p class="mt-3">In such cases WAYOUT checks, as far as it is competent, that the transfer takes place in accordance with Articles 44 and following of the GDPR, on the basis of a decision of adequacy of the European Commission, of the accession of the recipient to a framework recognized as appropriate, of the Standard Contractual Clauses approved by the European Commission or another valid mechanism, with any additional measures where necessary.</p>
        <p class="mt-3">Further information on the guarantees applied and, where available, a copy of the same can be requested by writing to <a class="font-black text-violet-700" href="mailto:amministrazione@wayoutapp.it">amministrazione@wayoutapp.it</a>.</p>
    </section>

    <section>
        <h2 class="text-2xl font-black text-slate-950">11. Automation and decision-making processes</h2>
        <p class="mt-3">After consent, Google Analytics 4 and Meta Pixel can process interactions with the site to produce statistics, measure campaigns and, in the case of Marketing tools, create segments or publics that can be used for promotional and remarketing activities. Such activities may fall within the notion of profiling, but do not in itself produce decisions with legal or similarly significant effects to the user.</p>
        <p class="mt-3">WAYOUT uses automated package availability controls, purchasing suitability, age, telephone verification, payment status and entities. These controls are aimed at performing the contract, preventing abuse and preventing invalid purchases and, according to the current provision, are not intended to produce decisions solely automated with legal or similarly significant effects within the meaning of Article 22 GDPR. In case of an abnormal outcome, the user may contact <a class="font-black text-violet-700" href="mailto:amministrazione@wayoutapp.it">amministrazione@wayoutapp.it</a> for a verification. Stripe can also use its anti-fraud systems according to its information.</p>
    </section>

    <section>
        <h2 class="text-2xl font-black text-slate-950">12. Minorities</h2>
        <p class="mt-3">The WAYOUT service and pre-sale are reserved for users aged at least 18. The form waitlist does not collect the date of birth; the requirement is verified in checkout and in subsequent account/activation flows. If WAYOUT becomes aware of data related to a minor in violation of the applicable conditions, it will take reasonable measures to erase or limit the processing, unless legal obligations or need for protection.</p>
    </section>

    <section>
        <h2 class="text-2xl font-black text-slate-950">13. Data security</h2>
        <p class="mt-3">WAYOUT adopts reasonable and risk-proportionate technical and organizational measures to protect data from loss, improper use, unauthorized access, alteration or disclosure. The measures include, according to the actual configuration, encrypted connections, session management and security tokens, limiting and permission of accesses, backups, logging, anti-spam controls and limiting installments.</p>
        <p class="mt-3">However, no computer system can guarantee absolute security. In case of violation of personal data, WAYOUT will adopt the measures provided for by applicable legislation, including, when required, the notification to the Guarantor and the communication to the interested parties.</p>
    </section>

    <section>
        <h2 class="text-2xl font-black text-slate-950">14. Rights of the interested party</h2>
        <p class="mt-3">In the cases and limits provided for by the GDPR, the data subject may exercise the following rights:</p>
        <ul class="mt-3 list-disc space-y-2 pl-6">
            <li>obtain confirmation that a processing and access to your data and information relating to the processing is either ongoing;</li>
            <li>obtain inaccurate data correction and incomplete data integration;</li>
            <li>obtain the deletion of data when the legal requirements apply;</li>
            <li>obtain restriction of treatment;</li>
            <li>receive data in structured format, of common use and readable by automatic device and transmit them to another holder, when the right to portability is applicable;</li>
            <li>to oppose, for reasons related to its particular situation, treatments based on legitimate interest;</li>
            <li>object at any time to the processing for direct marketing purposes;</li>
            <li>revoke consent at any time, without prejudice to the lawfulness of the processing carried out before revocation;</li>
            <li>not be subject to a decision based solely on automated processing in cases provided for in Article 22 of the GDPR;</li>
            <li>lodge a complaint with the Data Protection Authority or other competent supervisory authority.</li>
        </ul>
    </section>

    <section>
        <h2 class="text-2xl font-black text-slate-950">15. How to exercise rights</h2>
        <p class="mt-3">Requests can be sent to <a class="font-black text-violet-700" href="mailto:amministrazione@wayoutapp.it">amministrazione@wayoutapp.it</a> or the PEC <a class="font-black text-violet-700" href="mailto:wayout@pec.wayoutapp.it">wayout@pec.wayoutapp.it</a>. It is useful to indicate the right you intend to exercise, the email address used on the site and any information necessary to identify the data concerned.</p>
        <p class="mt-3">WAYOUT may request additional information reasonably necessary to verify the identity of the applicant. The reply is provided without undue delay and, as a rule, within a month of receipt of the request. This term may be extended for two more months in cases provided for by the GDPR, taking into account the complexity and number of requests; In this case the data subject is informed of the extension and of the related reasons within a month.</p>
        <p class="mt-3">The exercise of rights is normally free. In the presence of manifestly unfounded or excessive demands, in particular for their repetitive character, WAYOUT may apply the measures permitted by law.</p>
    </section>

    <section>
        <h2 class="text-2xl font-black text-slate-950">16. Reclaim to the Guarantor</h2>
        <p class="mt-3">The data subject who believes that the processing of his/her data infringes the applicable legislation can lodge a complaint with the Data Protection Authority in accordance with the methods indicated on the website <a class="font-black text-violet-700" href="https://www.garanteprivacy.it" target="_blank" rel="noopener noreferrer">www.garanteprivacy.it</a>, or contact the supervisory authority of the Member State in which he resides or works or where the alleged infringement occurred.</p>
    </section>

    <section>
        <h2 class="text-2xl font-black text-slate-950">17. Links and services of third parties</h2>
        <p class="mt-3">The site may contain links to Instagram, TikTok, Stripe or other external sites and services. The simple link does not involve, in itself, the installation on the site of pixels or tracking tools of the third party. Since the user accesses the external service, the processing is governed by the information and conditions of the relevant supplier.</p>
    </section>

    <section>
        <h2 class="text-2xl font-black text-slate-950">18. Privacy Policy Updates</h2>
        <p class="mt-3">WAYOUT can update this Privacy Policy to reflect regulatory, organizational, technical or service changes and suppliers used. The updated version will be published on the site with the date of review. In case of significant changes, WAYOUT may inform users through the site or through the available contact details, when appropriate.</p>
    </section>

    <section>
        <h2 class="text-2xl font-black text-slate-950">19. Effective date</h2>
        <p class="mt-3">This Privacy Policy is effective from the date of its publication on wayoutapp.it.</p>
    </section>
</div>
<section class="meta-conversions-api mt-8"><h2 class="text-xl font-black">Meta Conversions API</h2><p class="mt-3">When enabled in the website configuration, the Conversions API sends verified waitlist registrations and server-confirmed purchases to Meta only with valid prior Marketing consent, separate from consent to promotional emails. Data may include a normalized SHA-256-hashed email, IP address, user agent, _fbp and _fbc cookie identifiers when available, event name, time and identifier, a source page without tokens or confidential parameters and, for purchases, value and currency. Hashing does not make the email anonymous. Pixel and server share an event identifier to prevent double counting. Pending data is stored encrypted for up to seven days and event payloads are removed after delivery; checks and cleanup run through the website scheduler. Consent is checked again before every attempt: withdrawal through cookie preferences prevents subsequent deliveries associated with that consent. The purposes, recipients, transfers and rights described in the Privacy policy also apply.</p></section>
@endsection
