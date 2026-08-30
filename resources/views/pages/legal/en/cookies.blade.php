@extends('pages.legal.layout', [
    'title' => 'Cookie policy',
    'description' => 'Informativa sull’uso di cookie e strumenti simili sul sito WAYOUT.',
])

@section('legal-content')
<div class="space-y-8">
    <section>
        <h2 class="text-2xl font-black text-slate-950">Scope of the document</h2>
        <p class="mt-3">This Cookie Policy covers the wayoutapp.it website, including landing page, waitlist, pre-sale page, checkout, contact form and legal pages. It should be read together <a class="font-black text-violet-700" href="/privacy-policy">Privacy Policy</a> of the site, current version.</p>
    </section>

    <section>
        <h2 class="text-2xl font-black text-slate-950">1. Data controller and contacts</h2>
        <ul class="mt-3 list-disc space-y-2 pl-6">
            <li>WAYOUT S.r.l.;</li>
            <li>Tax code and VAT: 14805930964;</li>
            <li>registered office: Via Guglielmo Marconi 24/B, 20082 Binasco (MI), Italy;</li>
            <li>PEC: <a class="font-black text-violet-700" href="mailto:wayout@pec.wayoutapp.it">wayout@pec.wayoutapp.it</a>;</li>
            <li>email privacy: <a class="font-black text-violet-700" href="mailto:amministrazione@wayoutapp.it">amministrazione@wayoutapp.it</a>;</li>
            <li>General assistance: <a class="font-black text-violet-700" href="mailto:amministrazione@wayoutapp.it">amministrazione@wayoutapp.it</a>.</li>
        </ul>
        <p class="mt-3">For general information on the processing of personal data, recipients, transfers and rights of interested parties, please refer to <a class="font-black text-violet-700" href="/privacy-policy">Privacy Policy</a> published on the website.</p>
    </section>

    <section>
        <h2 class="text-2xl font-black text-slate-950">2. What are cookies and other tracking tools</h2>
        <p class="mt-3">Cookies are small text files that a third party site or service can store in your browser or device and read during your visit or in later accesses. They can be session when they are deleted at the end of the session or after a short period of inactivity, or persistent, when they are stored longer.</p>
        <p class="mt-3">The site can also use similar technologies, such as local storage, online identifiers, pixels, tags, URL parameters and network requests. In this policy the term “cookie” includes, where relevant, also such tools.</p>
    </section>

    <section>
        <h2 class="text-2xl font-black text-slate-950">3. Categories of instruments used</h2>
        <h3 class="mt-6 text-xl font-black text-slate-900">3.1 Strictly necessary tools</h3>
        <p class="mt-3">They allow navigation, form protection, session management, cookie preferences registration, email verification, checkout, Firebase/reCAPTCHA telephone verification, payment and fraud prevention. Their use does not require consent when it is limited to what is strictly necessary for the service requested and is not used for further purposes, subject to the obligation of information.</p>
        <h3 class="mt-6 text-xl font-black text-slate-900">3.2 Analytics</h3>
        <p class="mt-3">Google Tag Manager and Google Analytics 4 are uploaded only after your consent to the Analytics category. The configuration uses Google Allow Mode in Basic mode: Before interacting with the banner Google tags remain blocked and data is not transmitted to Google.</p>
        <h3 class="mt-6 text-xl font-black text-slate-900">3.3 Marketing and profiling</h3>
        <p class="mt-3">Meta Pixel browser side is activated only after consent to the Marketing category. At the date of this version Advanced Matching and Conversions API are disabled. Any future activations will require a prior update of the documentation and, when necessary, a new user choice.</p>
    </section>

    <section>
        <h2 class="text-2xl font-black text-slate-950">4. Management of consent</h2>
        <p class="mt-3">WAYOUT uses an internal CMP. Only the tools strictly necessary are active at first access. The banner allows, with clear commands and equal evidence, of:</p>
        <ul class="mt-3 list-disc space-y-2 pl-6">
            <li>accept all unnecessary tools;</li>
            <li>refuse all unnecessary tools and continue with only technical tools;</li>
            <li>customize the categories Analytics and Marketing separately;</li>
            <li>close the banner while keeping the default settings, without any unnecessary tracking.</li>
        </ul>
        <p class="mt-3">The scrolling, the continuation of navigation, silence or pre-selected boxes do not constitute consent. You can change or revoke your choices at any time by linking “Manage cookie preferences” available in the footer or by means of an equivalent command always accessible.</p>
        <p class="mt-3">The choice is stored by the technical cookie wayout_cookie_consent for 6 months and registered in a pseudonymous server-side log with date/hour, version of the CMP, preferences and hash of IP/user agent. The log is kept for 6 months, except for concrete disputes. The banner can be reproduced before expiry in case of cancellation of the cookie, modification requested by the user or significant changes in the purposes, categories or third parties.</p>
    </section>

    <section>
        <h2 class="text-2xl font-black text-slate-950">5. Cookies and tools strictly necessary</h2>
        <div class="mt-4 overflow-x-auto rounded-lg border border-slate-200">
            <table class="min-w-[900px] divide-y divide-slate-200 text-left text-sm">
                <thead class="bg-slate-50 font-black text-slate-700">
                    <tr>
                        <th class="px-4 py-3 align-top"><em><strong>Name / instrument</strong></em></th>
                        <th class="px-4 py-3 align-top"><em><strong>Supplier and domain</strong></em></th>
                        <th class="px-4 py-3 align-top"><em><strong>Purpose</strong></em></th>
                        <th class="px-4 py-3 align-top"><em><strong>Indicative duration</strong></em></th>
                        <th class="px-4 py-3 align-top"><em><strong>Category / consent</strong></em></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 bg-white">
                    <tr>
                        <td class="px-4 py-3 align-top font-black text-slate-900">wayout-session</td>
                        <td class="px-4 py-3 align-top">WAYOUT / wayoutapp.it</td>
                        <td class="px-4 py-3 align-top">It maintains the session and connects the forms, the waitlist, the pre-sale and the administrative area.</td>
                        <td class="px-4 py-3 align-top">120 minutes of inactivity, except for different production configuration</td>
                        <td class="px-4 py-3 align-top">Necessary - no consent</td>
                    </tr>
                    <tr>
                        <td class="px-4 py-3 align-top font-black text-slate-900">wayout_cookie_consent</td>
                        <td class="px-4 py-3 align-top">WAYOUT / Internal CMP</td>
                        <td class="px-4 py-3 align-top">Stores acceptance, rejection and granular preferences and allows you to apply your choice.</td>
                        <td class="px-4 py-3 align-top">6 months, subject to change or revocation</td>
                        <td class="px-4 py-3 align-top">Technical Preference - no consent</td>
                    </tr>
                    <tr>
                        <td class="px-4 py-3 align-top font-black text-slate-900">Token CSRF and security data</td>
                        <td class="px-4 py-3 align-top">WAYOUT / Laravel</td>
                        <td class="px-4 py-3 align-top">Protects forms and requests from unauthorized use. It can be stored in the session without autonomous cookie.</td>
                        <td class="px-4 py-3 align-top">Duration of session</td>
                        <td class="px-4 py-3 align-top">Necessary - no consent</td>
                    </tr>
                    <tr>
                        <td class="px-4 py-3 align-top font-black text-slate-900">Language Preference</td>
                        <td class="px-4 py-3 align-top">WAYOUT / wayoutapp.it</td>
                        <td class="px-4 py-3 align-top">Remember the selected language in the session.</td>
                        <td class="px-4 py-3 align-top">Duration of session</td>
                        <td class="px-4 py-3 align-top">Required functionality - no consent</td>
                    </tr>
                    <tr>
                        <td class="px-4 py-3 align-top font-black text-slate-900">_</td>
                        <td class="px-4 py-3 align-top">Stripe / Stripe domains</td>
                        <td class="px-4 py-3 align-top">Fraud prevention and transaction risk assessment.</td>
                        <td class="px-4 py-3 align-top">Up to 1 year</td>
                        <td class="px-4 py-3 align-top">Payment/anti-fraud - no consent</td>
                    </tr>
                    <tr>
                        <td class="px-4 py-3 align-top font-black text-slate-900">_</td>
                        <td class="px-4 py-3 align-top">Stripe / Stripe domains</td>
                        <td class="px-4 py-3 align-top">Fraud prevention and transaction risk assessment.</td>
                        <td class="px-4 py-3 align-top">About 30 minutes</td>
                        <td class="px-4 py-3 align-top">Payment/anti-fraud - no consent</td>
                    </tr>
                    <tr>
                        <td class="px-4 py-3 align-top font-black text-slate-900">Other anti-fraud identifiers Stripe</td>
                        <td class="px-4 py-3 align-top">Stripe / domains Stripe and m.stripe.network</td>
                        <td class="px-4 py-3 align-top">Safety and anti-fraud technical signals; names and duration may vary according to the Stripe flow.</td>
                        <td class="px-4 py-3 align-top">Variable session or duration according to Stripe</td>
                        <td class="px-4 py-3 align-top">Payment/anti-fraud - no consent</td>
                    </tr>
                    <tr>
                        <td class="px-4 py-3 align-top font-black text-slate-900">Firebase Phone Authentication / reCAPTCHA</td>
                        <td class="px-4 py-3 align-top">Google / Firebase</td>
                        <td class="px-4 py-3 align-top">Check phone number and prevention of abuse in checkout; can use network requests, tokens and technical storage.</td>
                        <td class="px-4 py-3 align-top">Session or technical duration necessary for verification; variable details according to Google configuration</td>
                        <td class="px-4 py-3 align-top">Required for verification/ authentication - no consent, if limited to the required function</td>
                    </tr>
                    <tr>
                        <td class="px-4 py-3 align-top font-black text-slate-900">wayout_event_* (localStorage/sessionStorage)</td>
                        <td class="px-4 py-3 align-top">WAYOUT / browser</td>
                        <td class="px-4 py-3 align-top">Technical deduplication of analytics/conversion events to avoid multiple sendings; does not contain email, phone or form data.</td>
                        <td class="px-4 py-3 align-top">Session or technical duration of the flag; deleted/updated according to the logical event</td>
                        <td class="px-4 py-3 align-top">Measurement support technician; Google/Meta events leave only after their consent</td>
                    </tr>
                </tbody>
            </table>
        </div>
        <p class="mt-3">Stripe.js and technical payment tools are loaded only when the user actually accesses the checkout, not the simple opening of the pre-sale page. Stripe Link is not enabled. The actual list of Stripe identifiers is checked periodically by technical scanning and browser tools.</p>
    </section>

    <section>
        <h2 class="text-2xl font-black text-slate-950">6. Google Tag Manager and Google Analytics 4</h2>
        <h3 class="mt-6 text-xl font-black text-slate-900">6.1 Google Tag Manager</h3>
        <p class="mt-3">Google Tag Manager is used to administer and distribute site tags and not as a user database. The container is loaded only after consent Analytics. Tags belonging to other categories can only be activated after specific consent to the relevant category.</p>
        <h3 class="mt-6 text-xl font-black text-slate-900">6.2 Google Analytics 4</h3>
        <p class="mt-3">After consent Analytics, WAYOUT uses Google Analytics 4 for site usage statistics and conversion measurement. The initial configuration includes Google Signals, advertising customization, User-ID, cross-domain tracking and Enhanced Conversions disabled. WAYOUT does not send Google emails, telephone number, tax code or other directly identifiable data.</p>
        <div class="mt-4 overflow-x-auto rounded-lg border border-slate-200">
            <table class="min-w-[900px] divide-y divide-slate-200 text-left text-sm">
                <thead class="bg-slate-50 font-black text-slate-700">
                    <tr>
                        <th class="px-4 py-3 align-top"><em><strong>Name / instrument</strong></em></th>
                        <th class="px-4 py-3 align-top"><em><strong>Supplier and domain</strong></em></th>
                        <th class="px-4 py-3 align-top"><em><strong>Purpose</strong></em></th>
                        <th class="px-4 py-3 align-top"><em><strong>Indicative duration</strong></em></th>
                        <th class="px-4 py-3 align-top"><em><strong>Category / consent</strong></em></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 bg-white">
                    <tr>
                        <td class="px-4 py-3 align-top font-black text-slate-900">_</td>
                        <td class="px-4 py-3 align-top">Google / wayoutapp.it</td>
                        <td class="px-4 py-3 align-top">Distinguish browsers and allow statistical measurement.</td>
                        <td class="px-4 py-3 align-top">Up to 2 years, except for configuration or browser limits</td>
                        <td class="px-4 py-3 align-top">Analytics - required consent</td>
                    </tr>
                    <tr>
                        <td class="px-4 py-3 align-top font-black text-slate-900">_</td>
                        <td class="px-4 py-3 align-top">Google / wayoutapp.it</td>
                        <td class="px-4 py-3 align-top">It maintains the session status for the specific Google Analytics 4 property.</td>
                        <td class="px-4 py-3 align-top">Up to 2 years, except for configuration or browser limits</td>
                        <td class="px-4 py-3 align-top">Analytics - required consent</td>
                    </tr>
                </tbody>
            </table>
        </div>
        <p class="mt-3">The event and user data retention period in the GA4 account is configured in 14 months; this period is distinct from the duration of cookies in the browser.</p>
    </section>

    <section>
        <h2 class="text-2xl font-black text-slate-950">7. Meta Pixel</h2>
        <p class="mt-3">After Marketing consent, WAYOUT uses Meta Pixel exclusively browser side to measure visits and conversions from campaigns, understand the effectiveness of advertising, create public and carry out any remarketing. Meta can receive online identifiers, browser and device information, IP address, page visited, referrer and events made on the site, even when you do not have a Facebook or Instagram account or are not authenticated.</p>
        <div class="mt-4 overflow-x-auto rounded-lg border border-slate-200">
            <table class="min-w-[900px] divide-y divide-slate-200 text-left text-sm">
                <thead class="bg-slate-50 font-black text-slate-700">
                    <tr>
                        <th class="px-4 py-3 align-top"><em><strong>Name / instrument</strong></em></th>
                        <th class="px-4 py-3 align-top"><em><strong>Supplier and domain</strong></em></th>
                        <th class="px-4 py-3 align-top"><em><strong>Purpose</strong></em></th>
                        <th class="px-4 py-3 align-top"><em><strong>Indicative duration</strong></em></th>
                        <th class="px-4 py-3 align-top"><em><strong>Category / consent</strong></em></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 bg-white">
                    <tr>
                        <td class="px-4 py-3 align-top font-black text-slate-900">_</td>
                        <td class="px-4 py-3 align-top">Meta Platforms / wayoutapp.it</td>
                        <td class="px-4 py-3 align-top">Measurement, campaign attribution, audience creation and Meta advertising.</td>
                        <td class="px-4 py-3 align-top">Up to 90 days, according to configuration and Meta policy</td>
                        <td class="px-4 py-3 align-top">Marketing/profiling - required consent</td>
                    </tr>
                    <tr>
                        <td class="px-4 py-3 align-top font-black text-slate-900">_</td>
                        <td class="px-4 py-3 align-top">Meta Platforms / wayoutapp.it</td>
                        <td class="px-4 py-3 align-top">Stores the advertising click identifier when the URL contains the fbclid parameter.</td>
                        <td class="px-4 py-3 align-top">Up to 90 days, if present</td>
                        <td class="px-4 py-3 align-top">Marketing/profiling - required consent</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </section>

    <section>
        <h2 class="text-2xl font-black text-slate-950">8. External links and social networks</h2>
        <p class="mt-3">The footer can contain simple links to WAYOUT profiles on Instagram and TikTok. In the absence of embedded social buttons, embed video, SDK or widget, the simple link does not determine the installation of social cookies on the WAYOUT website. After clicking and accessing the external platform, its provider processes data according to its own information.</p>
    </section>

    <section>
        <h2 class="text-2xl font-black text-slate-950">9. Transfers to countries not belonging to the EEA</h2>
        <p class="mt-3">The main hosting of the site through Aruba is configured in the European Union. Google, Meta, Stripe or some of their suppliers and subcontractors may however process or make data accessible even outside the European Economic Area. Transfers take place, according to their respective roles, on the basis of adequacy decisions, Standard Contractual Clauses or other mechanisms provided for in Articles 44 and following GDPR. Further details are available in <a class="font-black text-violet-700" href="/privacy-policy">Privacy Policy</a> and in the information of suppliers.</p>
    </section>

    <section>
        <h2 class="text-2xl font-black text-slate-950">10. How to manage cookies from the browser</h2>
        <p class="mt-3">You may delete or block cookies through your browser settings. The blocking of the instruments strictly necessary can prevent the proper functioning of the forms, the session, the pre-sale, the administrative area or the checkout. Browser settings do not replace the site preferences panel, which allows you to manage the Analytics and Marketing categories instantly.</p>
    </section>

    <section>
        <h2 class="text-2xl font-black text-slate-950">11. Update of inventory and Cookie Policy</h2>
        <p class="mt-3">Names, domains and durations of third-party cookies may change for updates of suppliers, browsers or technical configuration. WAYOUT periodically checks the inventory through scans and tests in the production environment and updates this policy and CMP when changing tools, purposes, categories, durations or third parties. In case of significant changes affecting the choices already expressed, the banner is redesigned or a new choice is required.</p>
    </section>

    <section>
        <h2 class="text-2xl font-black text-slate-950">12. Contact</h2>
        <p class="mt-3">For questions about this Cookie Policy or the use of tracking tools you can write to <a class="font-black text-violet-700" href="mailto:amministrazione@wayoutapp.it">amministrazione@wayoutapp.it</a>. To exercise the rights provided by the GDPR, please refer to <a class="font-black text-violet-700" href="/privacy-policy">Privacy Policy</a> of the site.</p>
        <p class="mt-3"><em><strong>Effective date: from the publication on the website wayoutapp.it.</strong></em></p>
    </section>
</div>
@endsection
