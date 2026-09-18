@extends('pages.legal.layout', [
    'title' => 'Termini di vendita',
    'description' => 'Condizioni generali applicabili alla formazione dell’ordine, al pagamento e alla gestione dell’acquisto online dei Founder Pass WAYOUT.',
])

@section('legal-content')
<div class="space-y-8">
    <section>
        <h2 class="text-2xl font-black uppercase text-slate-950">SCOPE</h2>
        <p class="mt-3">This document covers the online sales process of Founder Pass: pre-contractual information, order forwarding, payment through Stripe, contract conclusion, confirmation on durable medium, billing, assistance, withdrawal and remedies. The specific characteristics of the passes and the scenarios of the pre-sale remain governed also by the separate <a class="font-black text-violet-700" href="/condizioni-di-pre-sale">Specific pre-sale conditions</a>.</p>
    </section>

        <div class="mt-4 space-y-2 rounded-lg border border-violet-200 bg-violet-50/60 p-4">
            <p class="font-black text-slate-950"><strong>READ MORE</strong></p>
            <p class="text-slate-700">The purchase covers a digital subscription in pre-sale, with unique payment, activated by the public go-live of the app and the duration of 12 months from the correct individual activation. No automatic renewal is required. The Founder Pass does not include entrances, bookings, physical tables, drinks, consumptions or services provided by locals or other third parties.</p>
        </div>
    <section>
        <h2 class="text-2xl font-black text-slate-950">1. Definitions</h2>
        <ul class="mt-3 list-disc space-y-2 pl-6">
            <li>“WAYOUT” or “Seller”: WAYOUT S.r.l., company that sells Founder Pass and manages wayoutapp.it.</li>
            <li>“Acquirent” or “Consumer”: the natural person who acquires a Founder Pass for foreign purposes to his business, commercial, artisanal or professional activity.</li>
            <li>“Sito”: the wayoutapp.it website and its sales and checkout pages.</li>
            <li>“Pre-sale”: the sales phase before the public launch of the WAYOUT app.</li>
            <li>“Founder Pass”: Founder Join 12M or Founder Creator 12M, as described in <a class="font-black text-violet-700" href="/condizioni-di-pre-sale">Specific pre-sale conditions</a>.</li>
            <li>“Go-live”: the date when the WAYOUT app is publicly available for initial operation in the pilot city of Milan.</li>
            <li>“Order”: the purchase request sent by the Buyer via online checkout with payment obligation.</li>
            <li>“Stripe”: the external lender used for the technical management of payment and its anti-fraud instruments.</li>
            <li>“Sustainable Support”: a tool that allows the Consumer to store the information personally directed to him and reproduce it unchanged for an appropriate period, including the email with documents attached or downloadable in stable format.</li>
        </ul>
    </section>

    <section>
        <h2 class="text-2xl font-black text-slate-950">2. Identity of the seller and contacts</h2>
        <div class="mt-4 overflow-x-auto rounded-lg border border-slate-200">
            <table class="min-w-[900px] divide-y divide-slate-200 text-left text-sm">
                <thead class="bg-slate-50 font-black text-slate-700">
                    <tr>
                        <th class="px-4 py-3 align-top"><strong>Information</strong></th>
                        <th class="px-4 py-3 align-top"><strong>Date</strong></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 bg-white">
                    <tr>
                        <td class="px-4 py-3 align-top font-black text-slate-900">Description</td>
                        <td class="px-4 py-3 align-top">WAYOUT S.r.l.</td>
                    </tr>
                    <tr>
                        <td class="px-4 py-3 align-top font-black text-slate-900">Legal basis</td>
                        <td class="px-4 py-3 align-top">Via Guglielmo Marconi 24/B, 20082 Binasco (MI), Italy</td>
                    </tr>
                    <tr>
                        <td class="px-4 py-3 align-top font-black text-slate-900">VAT / Tax code</td>
                        <td class="px-4 py-3 align-top">14805930964</td>
                    </tr>
                    <tr>
                        <td class="px-4 py-3 align-top font-black text-slate-900">Email assistance</td>
                        <td class="px-4 py-3 align-top"><a class="font-black text-violet-700" href="mailto:amministrazione@wayoutapp.it">amministrazione@wayoutapp.it</a></td>
                    </tr>
                    <tr>
                        <td class="px-4 py-3 align-top font-black text-slate-900">Email orders, withdrawals and refunds</td>
                        <td class="px-4 py-3 align-top"><a class="font-black text-violet-700" href="mailto:amministrazione@wayoutapp.it">amministrazione@wayoutapp.it</a></td>
                    </tr>
                    <tr>
                        <td class="px-4 py-3 align-top font-black text-slate-900">PEC</td>
                        <td class="px-4 py-3 align-top"><a class="font-black text-violet-700" href="mailto:wayout@pec.wayoutapp.it">wayout@pec.wayoutapp.it</a></td>
                    </tr>
                    <tr>
                        <td class="px-4 py-3 align-top font-black text-slate-900">Phone</td>
                        <td class="px-4 py-3 align-top">+393522164100</td>
                    </tr>
                </tbody>
            </table>
        </div>
        <p class="mt-3"><strong>2.1 </strong>The Buyer can use the contacts indicated to obtain information before purchase and to communicate quickly and effectively with WAYOUT. Communications relating to an order must report, when available, the purchase email and the order identifier.</p>
        <p class="mt-3"><strong>2.2 </strong>The Buyer must not communicate to WAYOUT full numbers of card, security codes, passwords or payment credentials.</p>
    </section>

    <section>
        <h2 class="text-2xl font-black text-slate-950">3. Scope of application and contractual documents</h2>
        <p class="mt-3"><strong>3.1 </strong>These Terms govern only the online purchases of Founder Pass made through the Site by Senior Consumers located in Italy.</p>
        <p class="mt-3"><strong>3.2 </strong>They form an integral part of the relationship, in the version made available and accepted at the time of the Order:</p>
        <ul class="mt-3 list-disc space-y-2 pl-6">
            <li>the present <a class="font-black text-violet-700" href="/termini-di-vendita">Terms of sale online</a>;</li>
            <li>the <a class="font-black text-violet-700" href="/condizioni-di-pre-sale">Specific pre-sale conditions</a>;</li>
            <li>the page and summary of the selected Founder Pass;</li>
            <li>the<a class="font-black text-violet-700" href="/recedere-dal-contratto">Notice on the right of withdrawal</a> and the <a class="font-black text-violet-700" href="/recedere-dal-contratto">Refund Policy</a>;</li>
            <li>the <a class="font-black text-violet-700" href="/privacy-policy">Privacy Policy</a>, as regards the processing of personal data;</li>
            <li>After the go-live, the App Terms, the Community Guidelines and the Safety Policy applicable to the use of the platform.</li>
        </ul>
        <p class="mt-3"><strong>3.3 </strong>In the event of a conflict, these Terms prevail for rules concerning the order, payment, conclusion of the contract and invoicing; the <a class="font-black text-violet-700" href="/condizioni-di-pre-sale">Specific pre-sale terms</a> prevail for the characteristics, duration, activation, limitations and scenarios of the Founder Pass. Mandatory consumer-protection rules prevail in all cases.</p>
        <p class="mt-3"><strong>3.4 </strong>The purchase does not attribute any further rights than those expressly described in the contractual documents and does not transform WAYOUT into event organizer, local intermediary or third party service seller.</p>
    </section>

    <section>
        <h2 class="text-2xl font-black text-slate-950">4. Requirements to make purchase</h2>
        <p class="mt-3"><strong>4.1 </strong>The purchase is reserved for persons who are 18 years old, have the ability to conclude the contract and use a legitimately available means of payment.</p>
        <p class="mt-3"><strong>4.2 </strong>Pre-sale is accessible to users registered in the waitlist or otherwise enabled by WAYOUT according to the Website's flow. Access to the page does not guarantee the availability of the pass until payment is completed.</p>
        <p class="mt-3"><strong>4.3 </strong>The Buyer must provide complete, true, up-to-date and personal data, including name, surname, date of birth, phone number and email. WAYOUT may request error correction or missing information necessary for order, invoice or activation.</p>
        <p class="mt-3"><strong>4.4 </strong>Purchases made by minors, by means of false data, automated instruments, identity of others, unauthorized means of payment or fraudulent conduct may be refused or cancelled, subject to the refund of the amounts due by law and the adoption of the necessary anti-fraud measures.</p>
    </section>

    <section>
        <h2 class="text-2xl font-black text-slate-950">5. Main features and prices</h2>
        <div class="mt-4 overflow-x-auto rounded-lg border border-slate-200">
            <table class="min-w-[900px] divide-y divide-slate-200 text-left text-sm">
                <thead class="bg-slate-50 font-black text-slate-700">
                    <tr>
                        <th class="px-4 py-3 align-top"><strong>Piano</strong></th>
                        <th class="px-4 py-3 align-top"><strong>Total price</strong></th>
                        <th class="px-4 py-3 align-top"><strong>Duration and decor</strong></th>
                        <th class="px-4 py-3 align-top"><strong>Maximum</strong></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 bg-white">
                    <tr>
                        <td class="px-4 py-3 align-top font-black text-slate-900">Founder Join 12M</td>
                        <td class="px-4 py-3 align-top">€29, VAT and applicable charges included</td>
                        <td class="px-4 py-3 align-top">12 months from the correct individual activation, possible from the go-live; single payment; no automatic renewal</td>
                        <td class="px-4 py-3 align-top">Up to 500 passes</td>
                    </tr>
                    <tr>
                        <td class="px-4 py-3 align-top font-black text-slate-900">Founder Creator 12M</td>
                        <td class="px-4 py-3 align-top">€59, VAT and applicable charges included</td>
                        <td class="px-4 py-3 align-top">12 months from the correct individual activation, possible from the go-live; single payment; no automatic renewal</td>
                        <td class="px-4 py-3 align-top">Up to 150 passes</td>
                    </tr>
                </tbody>
            </table>
        </div>
        <p class="mt-3"><strong>5.1 </strong>The total price is shown in euro before the Order is forwarded and includes VAT and other tax charges applicable. There are no automatic renewal fees or fees applied directly by WAYOUT for payment.</p>
        <p class="mt-3"><strong>5.2 </strong>Functions, exclusions, quantitative caps, the overall economic cap of the Pre-sale and the launch scenarios are described in <a class="font-black text-violet-700" href="/condizioni-di-pre-sale">Specific pre-sale conditions</a>.</p>
        <p class="mt-3"><strong>5.3 </strong>The Founder Pass is personal, non-transferable and intended to be associated with the Buyer’s account. Founder Creator 12M does not require further discretionary selection than the standard registration and activation requirements for users.</p>
        <p class="mt-3"><strong>5.4 </strong>The mere display or selection of a plan does not reserve its availability. Any technical reservation during checkout is temporary and may expire if payment is not completed.</p>
    </section>

    <section>
        <h2 class="text-2xl font-black text-slate-950">6. Information before order</h2>
        <p class="mt-3"><strong>6.1 </strong>Before being bound, the Buyer can consult and save the information related to the identity and contacts of the Seller, characteristics of the Founder Pass, total price, duration, addition, payment mode, automatic renewal, withdrawal right, refunds, assistance, compliance of the digital service and major exclusions.</p>
        <p class="mt-3"><strong>6.2 </strong>Immediately before the payment, the checkout clearly shows at least the selected plan, the total price, the duration of 12 months from the correct individual activation of the account in the app (activable from the go-live), the absence of automatic renewal, the nature of pre-sale and the links to the applicable contractual documents.</p>
        <p class="mt-3"><strong>6.3 </strong>The Buyer is required to read the summary and related documents before proceeding. The <a class="font-black text-violet-700" href="/privacy-policy">Privacy Policy</a> constitutes information on data processing and does not replace the acceptance of contractual documents.</p>
    </section>

    <section>
        <h2 class="text-2xl font-black text-slate-950">7. Technical steps to conclude purchase</h2>
        <div class="mt-4 overflow-x-auto rounded-lg border border-slate-200">
            <table class="min-w-[900px] divide-y divide-slate-200 text-left text-sm">
                <thead class="bg-slate-50 font-black text-slate-700">
                    <tr>
                        <th class="px-4 py-3 align-top"><strong>Phase</strong></th>
                        <th class="px-4 py-3 align-top"><strong>Operation</strong></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 bg-white">
                    <tr>
                        <td class="px-4 py-3 align-top font-black text-slate-900">1. Access</td>
                        <td class="px-4 py-3 align-top">Insert or acknowledge the email associated with the waitlist and access to the Pre-sale page.</td>
                    </tr>
                    <tr>
                        <td class="px-4 py-3 align-top font-black text-slate-900">2. Choice of plan</td>
                        <td class="px-4 py-3 align-top">Selection of Founder Join 12M or Founder Creator 12M, within the limits of availability.</td>
                    </tr>
                    <tr>
                        <td class="px-4 py-3 align-top font-black text-slate-900">3. Data verification</td>
                        <td class="px-4 py-3 align-top">Entering or confirming your personal data, age and phone number.</td>
                    </tr>
                    <tr>
                        <td class="px-4 py-3 align-top font-black text-slate-900">4. Optional invoice</td>
                        <td class="px-4 py-3 align-top">Selection of the option to request invoice and insertion or confirmation of name, surname, tax code, address and civic number, CAP, municipality, province, state and email to which to send the copy of the invoice.</td>
                    </tr>
                    <tr>
                        <td class="px-4 py-3 align-top font-black text-slate-900">5. Summary and Acceptance</td>
                        <td class="px-4 py-3 align-top">Control of essential information and completion of mandatory statements and acceptances.</td>
                    </tr>
                    <tr>
                        <td class="px-4 py-3 align-top font-black text-slate-900">6. Payment</td>
                        <td class="px-4 py-3 align-top">Activation of the button that unequivocally indicates the obligation to pay and transfer to Stripe Checkout.</td>
                    </tr>
                    <tr>
                        <td class="px-4 py-3 align-top font-black text-slate-900">7. Exit</td>
                        <td class="px-4 py-3 align-top">Displaying the outcome of payment and, if successful, registration of the Order and sending confirmation via email.</td>
                    </tr>
                </tbody>
            </table>
        </div>
        <p class="mt-3"><strong>7.1 </strong>Before sending, the Buyer can correct the data using the form commands, return to the plan selection or stop the checkout without closing the purchase.</p>
        <p class="mt-3"><strong>7.2 After forwarding, any errors in data must be reported without delay to <a class="font-black text-violet-700" href="mailto:amministrazione@wayoutapp.it">amministrazione@wayoutapp.it</a> or <a class="font-black text-violet-700" href="mailto:amministrazione@wayoutapp.it">amministrazione@wayoutapp.it</a>. Corrections are possible within the technical, tax and anti-fraud limits applicable and do not allow to transfer the pass to a different person.</strong></p>
        <p class="mt-3"><strong>7.3 </strong>The contract is concluded in Italian. WAYOUT electronically stores the essential data of the Order and the version of the agreed contractual documents; the Buyer may request copies of the contacts indicated.</p>
        <p class="mt-3"><strong>7.4 </strong>WAYOUT does not adhere, in the state, to specific codes of conduct relating to the sales process, unless otherwise communicated on the Site.</p>
    </section>

    <section>
        <h2 class="text-2xl font-black text-slate-950">8. Passage to payment and obligation on the final command</h2>
        <p class="mt-3"><strong>8.1 </strong>On the WAYOUT website, the “Confirm and Payment” command validates the data, creates or recognizes the account, checks availability and suitability, records a provisional order and transfers the Buyer to the Stripe checkout. This command is not yet the final command with payment obligation. The obligation to pay must be easily readable and unambiguous by the final command shown by Stripe with the total price.</p>
        <p class="mt-3"><strong>8.2 </strong>Before transfer to Stripe, WAYOUT verifies the package available in the backend catalog, 18+ requirement, phone number via Firebase, account creation/recognition and purchasing suitability. The price displayed in the summary must coincide with that returned from the catalog and saved in the order; in case of inconsistency checkout must be blocked.</p>
        <p class="mt-3"><strong>8.3 </strong>If the Buyer does not complete the payment, the contract does not end and the temporary availability may be released. WAYOUT does not charge any amount for an abandoned checkout.</p>
    </section>

    <section>
        <h2 class="text-2xl font-black text-slate-950">9. Conclusion of the contract</h2>
        <p class="mt-3"><strong>9.1 </strong>The contract ends when the payment is completed in the checkout Stripe and WAYOUT, through its backend, check the active entitlement and register the Order with positive status. The date and time of such registration constitute the date of conclusion of the purchase, save evidence of a different moment resulting from the payment systems.</p>
        <p class="mt-3"><strong>9.2 </strong>The success page has informative function. In case of inconsistency, the actual status of payment, the registration of the Order and the confirmation sent by WAYOUT prevails.</p>
        <p class="mt-3"><strong>9.3 </strong>If a payment is authorized or cashed but, for simultaneous purchases, technical error, duplication or overcoming the capacity, the Order cannot be validly registered, WAYOUT cancels the purchase and fully refunds the amount without cost and without undue delay, as a rule on the same means of payment.</p>
        <p class="mt-3"><strong>9.4 </strong>WAYOUT may suspend the finalization of the Order for the time strictly necessary to carry out anti-fraud or security checks. If the check does not confirm the purchase, the Order is canceled and the due sums are refunded.</p>
        <p class="mt-3"><strong>9.5 </strong>A manifestly recognizable price or description error can result in correction before the conclusion of the contract. If the error is detected after a charge and makes it impossible to execute under clearly incorrect conditions, WAYOUT informs the Buyer and proceeds to full refund, stop the mandatory suits.</p>
    </section>

    <section>
        <h2 class="text-2xl font-black text-slate-950">10. Payment via Stripe</h2>
        <p class="mt-3"><strong>10.1 </strong>The payment is managed via Stripe Checkout, whose session is created by the WAYOUT backend after the site's preliminary checks. The methods actually available are those displayed in checkout and may include payment cards or additional tools enabled by Stripe and WAYOUT.</p>
        <p class="mt-3"><strong>10.2 </strong>WAYOUT does not directly store full card data. Stripe and any providers of the chosen method process payment data according to their own terms and information and can carry out authentication, security and fraud prevention checks.</p>
        <p class="mt-3"><strong>10.3 </strong>The Buyer guarantees to be authorized to use the selected payment method. The refusal, revocation or lack of authentication of payment prevent the conclusion of the Order.</p>
        <p class="mt-3"><strong>10.4 </strong>The currency of the purchase is the euro. Any costs applied independently by the bank, the issuer or the lender of payment of the Buyer are not charged by WAYOUT and remain subject to the relationship between the Buyer and that person.</p>
    </section>

    <section>
        <h2 class="text-2xl font-black text-slate-950">11. Order confirmation on durable medium</h2>
        <p class="mt-3"><strong>11.1 </strong>After the conclusion of the contract, WAYOUT sends without undue delay to the email used for the purchase a confirmation of the Order on durable medium.</p>
        <p class="mt-3"><strong>11.2 </strong>Confirmation contains or makes available in stable and saveable format at least: identification of the Order, date, plan purchased, total price, method or condition of payment, duration, individual activation rule from the go-live, absence of automatic renewal, data of the Seller, information on withdrawal and unchangeable copy or attachment of the contractual documents accepted with relative version/date.</p>
        <p class="mt-3"><strong>11.3 </strong>The Buyer must check the confirmation and promptly report any inconsistencies. The failure to receive the email does not cancel a contract already concluded, but the Buyer can ask for the return via the assistance channels.</p>
        <p class="mt-3"><strong>11.4 </strong>WAYOUT retains the technical evidence of the purchase, including date and time, identification of the Order, plan, price, payment status and version of the accepted documents, in compliance with the <a class="font-black text-violet-700" href="/privacy-policy">Privacy Policy</a> and legal obligations.</p>
    </section>

    <section>
        <h2 class="text-2xl font-black text-slate-950">12. Tax billing and documentation</h2>
        <p class="mt-3"><strong>12.1 </strong>Confirmation of the Order does not in itself constitute an invoice. The Buyer who wishes to receive invoice must select the appropriate option before payment. For physical person, the data provided by the form are required; for a legal person may be required social reason, VAT, address, CAP, municipality, province, state, PEC and SDI address code where applicable. The data must be verified before payment.</p>
        <p class="mt-3"><strong>12.2 </strong>WAYOUT may require the integration of missing data essential for the proper issue of the tax document. The Buyer is responsible for the accuracy and completeness of the data communicated and must report any errors without delay.</p>
        <p class="mt-3"><strong>12.3 </strong>The electronic invoice, when requested, is managed by Qonto according to applicable tax law; creation, transmission and possible errors are reconciled to WAYOUT systems. Late or modified requests are managed within the limits permitted by the tax discipline and the systems used.</p>
        <p class="mt-3"><strong>12.4 </strong>In the absence of an invoice request, WAYOUT issues and retains the documentation provided for by applicable law and sends the Order’s contractual confirmation.</p>
    </section>

    <section>
        <h2 class="text-2xl font-black text-slate-950">13. Contract execution and activation of Founder Pass</h2>
        <p class="mt-3"><strong>13.1 </strong>Payment improves early purchase of Founder Pass. The pass can only be activated from the public go-live of the app in the pilot city of Milan and becomes usable when the Buyer correctly completes the activation of his account in the app.</p>
        <p class="mt-3"><strong>13.2 </strong>The duration of 12 months starts from the correct individual activation of the Founder Pass on the account, not from the date of payment and not automatically from the go-live. Activation cannot occur before the go-live: the Buyer does not lose days for the period between the go-live and its proper activation.</p>
        <p class="mt-3"><strong>13.3 </strong>The activation requires the completion of the standard onboarding of the app, including verification of the phone number, age, truthful data, real personal photography and compliance and acceptance of the applicable rules. There is no additional discretionary gate for Founder Creator than Founder Join.</p>
        <p class="mt-3"><strong>13.4 </strong>The 12-month period does not begin until the Buyer correctly completes activation. If activation is prevented by a problem attributable to WAYOUT, support or the other remedies under the <a class="font-black text-violet-700" href="/condizioni-di-pre-sale">Specific Pre-sale Terms</a> and the rules on digital services apply.</p>
    </section>

    <section>
        <h2 class="text-2xl font-black text-slate-950">14. Right of withdrawal</h2>
        <p class="mt-3"><strong>14.1 </strong>The Consumer may withdraw from the purchase without indicating the reason within 14 days of the conclusion of the contract.</p>
        <p class="mt-3"><strong>14.2 </strong>The withdrawal can be exercised through the online function “Withdraw from the contract here”, available in a visible and continuous way during the withdrawal period at the address <a class="font-black text-violet-700" href="https://wayoutapp.it/recedere-dal-contratto">https://wayoutapp.it/recedere-dal-contratto</a>, or by an explicit declaration sent to <a class="font-black text-violet-700" href="mailto:amministrazione@wayoutapp.it">amministrazione@wayoutapp.it</a> or the PEC <a class="font-black text-violet-700" href="mailto:wayout@pec.wayoutapp.it">wayout@pec.wayoutapp.it</a>.</p>
        <p class="mt-3"><strong>14.3 </strong>The online function requires at least the name of the Buyer, the identification information of the Order and the electronic delivery for confirmation. The final sending takes place by means of a “Confirm withdrawal” or equivalent unequivocal.</p>
        <p class="mt-3"><strong>14.4 </strong>WAYOUT sends without undue delay a receipt on durable medium containing the received statement and its date and time. The term is respected if the Consumer transmits communication before the expiry of 14 days.</p>
        <p class="mt-3"><strong>14.5 </strong>WAYOUT fully refunds payments received at no cost and without undue delay, however within 14 days from the day on which it is informed of withdrawal, using the same means of payment unless otherwise expressed agreement and without fees charged to the Consumer.</p>
        <p class="mt-3"><strong>14.6 </strong>Consistent with <a class="font-black text-violet-700" href="/condizioni-di-pre-sale">Specific pre-sale conditions</a>, WAYOUT recognizes the full refund for the timely withdrawal even if the go-live has intervened during the period of 14 days, without retaining a proportional amount for the period possibly incurred, except for a future modification expressly more favorable or required by the Consumer and complying with the law.</p>
        <p class="mt-3"><strong>14.7 </strong>Complete instructions and withdrawal model are contained in the separate <a class="font-black text-violet-700" href="/recedere-dal-contratto">Notice on the right of withdrawal</a> and on the page <a class="font-black text-violet-700" href="/recedere-dal-contratto">Withdrawal and refunds</a>; the online function is available at the address <a class="font-black text-violet-700" href="https://wayoutapp.it/recedere-dal-contratto">https://wayoutapp.it/recedere-dal-contratto</a>.</p>
    </section>

    <section>
        <h2 class="text-2xl font-black text-slate-950">15. Additional refunds and scenarios of the Pre-Sales</h2>
        <p class="mt-3"><strong>15.1 </strong>In addition to withdrawal, refunds are available in the cases and under the procedures set out in the <a class="font-black text-violet-700" href="/condizioni-di-pre-sale">Specific Pre-sale Terms</a> and the <a class="font-black text-violet-700" href="/recedere-dal-contratto">Refund Policy</a>, including failure to launch, the passing of 31 December 2026 where the Buyer requests a refund, a substantial detrimental change rejected within the stated period, and a permanent inability to activate that is not attributable to the Buyer.</p>
        <p class="mt-3"><strong>15.2 </strong>If the app is not publicly available in Milan by 31 December 2026, the Buyer can choose whether to keep the Founder Pass for the next go-live or request full refund. In the absence of request, the pass remains valid and can be activated by the next go-live; the 12 months will decorate from the correct individual activation.</p>
        <p class="mt-3"><strong>15.3 </strong>In case of substantial and worsening change before the go-live, the Buyer may accept the change or request a refund within 30 days of the notice. No automatic refund is required for the only course of the term.</p>
        <p class="mt-3"><strong>15.4 </strong>They do not give themselves the right to reimbursement: delays within the maximum date, non-voluntary use, non-participation or acceptance in specific tables, absence of the desired social result, lack of availability of certain events, growth of the community lower than expectations or suspension correctly arranged for violations attributable to the user, without prejudice to undue rights.</p>
        <p class="mt-3"><strong>15.5 </strong>Refunds other than withdrawal are processed, as a rule, within 14 days of acceptance of the request or communication that determines the right and the same means of payment, unless technical impossibility or different agreement.</p>
    </section>

    <section>
        <h2 class="text-2xl font-black text-slate-950">16. Compliance and remedies related to digital service</h2>
        <p class="mt-3"><strong>16.1 </strong>From go-live, WAYOUT is responsible for providing digital service in accordance with the contract and mandatory legislation applicable to digital content and services.</p>
        <p class="mt-3"><strong>16.2 </strong>In the event of failure to supply or lack of conformity, the Consumer may request the restoration of conformity and, when the conditions are met, the proportional reduction of the price or the termination of the contract, as well as the additional remedies provided by law.</p>
        <p class="mt-3"><strong>16.3 </strong>The Buyer must reasonably cooperate with the technical verification of the problem, within the limits of non-invasive and respectful means of privacy. Failure to supply or defect must be reported through assistance contacts with the information necessary to identify the Order and the problem.</p>
        <p class="mt-3"><strong>16.4 </strong>No provision of these Terms limits or excludes undue rights recognised by the Consumer.</p>
    </section>

    <section>
        <h2 class="text-2xl font-black text-slate-950">17. Cancellation, suspension and resolution for Acquirent conduct</h2>
        <p class="mt-3"><strong>17.1 </strong>WAYOUT may cancel an Order or prevent activation in case of minor age, fraud, use of identity or other means of payment, intentionally false data, abusive duplication or serious violations of contractual documents, in compliance with proportionality and applicable safeguards.</p>
        <p class="mt-3"><strong>17.2 </strong>After the go-live, suspension and closing of the account are also governed by the App Terms, the Community Guidelines and the Safety Policy. A correct suspension attributable to the user can result in loss of residual access without refund, without prejudice to mandatory rights and the assessment of the concrete case.</p>
        <p class="mt-3"><strong>17.3 </strong>If a measure is recognized as incorrect after review, WAYOUT adopts a proportional remedy, such as reactivation, extension of the pass or appropriate refund.</p>
    </section>

    <section>
        <h2 class="text-2xl font-black text-slate-950">18. Site availability, technical errors and greater strength</h2>
        <p class="mt-3"><strong>18.1 </strong>WAYOUT adopts reasonable measures to keep the purchase flow available and secure, but can temporarily suspend it for maintenance, updates, accidents, safety, unavailable suppliers or force majeure.</p>
        <p class="mt-3"><strong>18.2 </strong>A malfunction of the Site does not charge if payment has not been authorized. In case of doubt about the outcome, the Buyer must avoid repeating the payment immediately and verify the email or contact assistance to prevent duplication.</p>
        <p class="mt-3"><strong>18.3 </strong>WAYOUT does not respond to delays or disservices solely attributable to the bank, to Stripe, to the Buyer’s network or device, unless the Seller’s own obligations and undue remedies. Indebtedly or doublely cashed amounts are refunded after verification.</p>
    </section>

    <section>
        <h2 class="text-2xl font-black text-slate-950">19. Responsibility and services of third parties</h2>
        <p class="mt-3"><strong>19.1 </strong>The limitations on the social nature of the platform, relationships with users and locals, offline interactions and excluded services are contained in <a class="font-black text-violet-700" href="/condizioni-di-pre-sale">Specific pre-sale conditions</a> and in the Terms of the app.</p>
        <p class="mt-3"><strong>19.2 </strong>Stripe and any payment method providers are independent third parties for their own activities. WAYOUT remains responsible for the sales obligations that the law imposes on it and assists the Buyer in the management of the Order and the refunds due.</p>
        <p class="mt-3"><strong>19.3 </strong>To the extent permitted by law, WAYOUT is not liable for damage caused exclusively by incorrect data provided by the Buyer, unauthorised use of the Buyer’s device or payment method, third-party conduct or breaches of the contractual documents. Liabilities that cannot be excluded remain unaffected, including liability for wilful misconduct or gross negligence.</p>
    </section>

    <section>
        <h2 class="text-2xl font-black text-slate-950">20. Processing of personal data</h2>
        <p class="mt-3"><strong>20.1 </strong>Personal data are processed to manage access to Pre-sale, Order, Payment, Invoicing, Confirmation, Assistance, Refunds, Fraud Prevention, Tax Compliance and Rights Protection, according to <a class="font-black text-violet-700" href="/privacy-policy">Privacy Policy</a> of the Site.</p>
        <p class="mt-3"><strong>20.2 </strong>Stripe processes the payment data according to the roles and purposes described in its documentation. WAYOUT does not use full card data for autonomous purposes.</p>
        <p class="mt-3"><strong>20.3 </strong>Any marketing consent is optional, separated from purchase and revoked. The refusal or revocation does not prevent purchase or strictly contractual communications.</p>
    </section>

    <section>
        <h2 class="text-2xl font-black text-slate-950">21. Assistance, complaints and communications</h2>
        <p class="mt-3"><strong>21.1. For general assistance the Buyer can write to <a class="font-black text-violet-700" href="mailto:amministrazione@wayoutapp.it">amministrazione@wayoutapp.it</a> or contact the number. For orders, withdrawals, refunds and administrative aspects may write to <a class="font-black text-violet-700" href="mailto:amministrazione@wayoutapp.it">amministrazione@wayoutapp.it</a>. formal communications can be sent to the PEC <a class="font-black text-violet-700" href="mailto:wayout@pec.wayoutapp.it">wayout@pec.wayoutapp.it</a>.</strong></p>
        <p class="mt-3"><strong>21.2 </strong>WAYOUT sends contractual communications to the Order’s associated email. The Buyer must keep it accessible, check the spam folder and communicate any changes.</p>
        <p class="mt-3"><strong>21.3 </strong>Complaints must describe the problem and indicate the purchase email and the order identifier. WAYOUT confirms its receipt and manages them within reasonable time, depending on complexity and without prejudice to the terms provided by law.</p>
        <p class="mt-3"><strong>21.4 </strong>If a complaint is not resolved, WAYOUT will provide, when required, information on alternative dispute resolution bodies and will indicate whether it intends to participate in the relevant procedure. The Consumer's right to appeal to the competent court shall always be subject to the right of the consumer.</p>
    </section>

    <section>
        <h2 class="text-2xl font-black text-slate-950">22. Version, storage and modification of the Terms</h2>
        <p class="mt-3"><strong>22.1 </strong>The applicable version is that made available and accepted at the time of the Order. WAYOUT registers the version or a unique identifier and makes it available in the contractual confirmation in conservative format.</p>
        <p class="mt-3"><strong>22.2 </strong>WAYOUT can update the Terms for future orders by publishing a new version and update date. The amendments do not apply retroactively to the Orders already concluded, except for legal provisions, measures necessary for the safety or agreement expressed with the Buyer, without prejudice to the rights accrued.</p>
        <p class="mt-3"><strong>22.3 </strong>If a clause is nothing or ineffective, the others remain valid. The clause concerned shall be replaced within the limits permitted by the applicable legal provision and the original economic purpose.</p>
    </section>

    <section>
        <h2 class="text-2xl font-black text-slate-950">23. Applicable law and jurisdiction</h2>
        <p class="mt-3"><strong>23.1 </strong>These Terms and Contracts are governed by Italian law, subject to the application of the mandatory norms possibly more favourable than the law of the country of habitual residence of the Consumer.</p>
        <p class="mt-3"><strong>23.2 </strong>For disputes with a Consumer, the Judge of the place of residence or domicile of the Consumer shall be competent, where appropriate.</p>
        <p class="mt-3"><strong>23.3 </strong>The Italian version is the reference text. Any translations are provided for convenience and must be interpreted in accordance with the Italian version and mandatory norms.</p>
    </section>

    <section>
        <h2 class="text-2xl font-black text-slate-950">24. Final references and referrals</h2>
        <p class="mt-3"><strong>24.1 </strong>For the characteristics of the Founder Pass, the maximum launch date, changes in the service, exclusions and further refunds refer to <a class="font-black text-violet-700" href="/condizioni-di-pre-sale">Specific pre-sale conditions</a>.</p>
        <p class="mt-3"><strong>24.2 </strong>For the right of withdrawal, the online function and the form refer to<a class="font-black text-violet-700" href="/recedere-dal-contratto">Notice on the right of withdrawal</a> and the page <a class="font-black text-violet-700" href="/recedere-dal-contratto">Withdrawal and refunds</a>. The online function is available at the address <a class="font-black text-violet-700" href="https://wayoutapp.it/recedere-dal-contratto">https://wayoutapp.it/recedere-dal-contratto</a>.</p>
        <p class="mt-3"><strong>24.3 </strong>For the processing of personal data and the use of cookies or similar tools, please refer to <a class="font-black text-violet-700" href="/privacy-policy">Privacy Policy</a>, <a class="font-black text-violet-700" href="/cookie-policy">Cookie Policy</a> and the preferences center.</p>
        <p class="mt-3"><strong>24.4 </strong>These Terms shall be prepared in accordance with the Italian legislation applicable to distance contracts, electronic commerce and digital services, without prejudice to subsequent legislative changes.</p>
    </section>
</div>
@endsection
