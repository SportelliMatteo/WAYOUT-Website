@extends('pages.legal.layout', [
    'title' => 'Refund Policy',
    'description' => 'Documento pubblico destinato al sito wayoutapp.it e al flusso di acquisto dei Founder Pass WAYOUT.',
])

@section('legal-content')
<div class="space-y-8">
        <div class="mt-4 space-y-2 rounded-lg border border-slate-200 bg-slate-50 p-4">
            <p class="font-black text-slate-950"><strong>SCOPE</strong></p>
            <p class="text-slate-700">This page explains the right of withdrawal, the digital function for exercising it and the additional cases in which the buyer of a Founder Pass can obtain a refund. Integrates <a class="font-black text-violet-700" href="/termini-di-vendita">Terms of sale online</a> and <a class="font-black text-violet-700" href="/condizioni-di-pre-sale">Specific pre-sale conditions</a>, which remain applicable in respect of aspects not covered here.</p>
        </div>
        <div class="mt-4 space-y-2 rounded-lg border border-violet-200 bg-violet-50/60 p-4">
            <p class="font-black text-slate-950"><strong>AT A GLANCE</strong></p>
            <p class="text-slate-700">The withdrawal within 14 days does not require motivation. The additional refunds depend on the concrete case: failure to launch, overcoming the maximum date, substantial change and worsening, definitive impossibility of activation, payment error or remedies provided by law for digital services.</p>
        </div>
    <section>
        <h2 class="text-2xl font-black text-slate-950">1. Owner of the sale and contacts</h2>
        <p class="mt-3"><strong>1.1 </strong>The seller of Founder Pass is WAYOUT S.r.l., with registered office in Via Guglielmo Marconi 24/B, 20082 Binasco (MI), Italy, tax code and VAT number 14805930964.</p>
        <p class="mt-3"><strong>1.2 For requests relating to orders, assistance, withdrawal and refunds you can write to <a class="font-black text-violet-700" href="mailto:amministrazione@wayoutapp.it">amministrazione@wayoutapp.it</a>. formal communications can be sent to the PEC <a class="font-black text-violet-700" href="mailto:wayout@pec.wayoutapp.it">wayout@pec.wayoutapp.it</a>.</strong></p>
    </section>

    <section>
        <h2 class="text-2xl font-black text-slate-950">2. Difference between withdrawal and refund request</h2>
        <p class="mt-3"><strong>2.1 </strong>The withdrawal is the right of the consumer to dissolve the contract within the period prescribed by law, without having to indicate the reason.</p>
        <p class="mt-3"><strong>2.2 </strong>A refund request concerns the additional cases provided for by the <a class="font-black text-violet-700" href="/condizioni-di-pre-sale">Specific pre-sale terms</a>, the <a class="font-black text-violet-700" href="/termini-di-vendita">Online sales terms</a> or applicable law, including after the ordinary withdrawal period has expired.</p>
        <p class="mt-3"><strong>2.3 </strong>The sending of a request does not automatically entail the recognition of the refund when it depends on the verification of the relative assumptions. WAYOUT confirms the receipt and communicates the outcome after the necessary verifications, without prejudice to the undue rights of the consumer.</p>
    </section>

    <section>
        <h2 class="text-2xl font-black text-slate-950">3. Synthetic framework of cases</h2>
        <div class="mt-4 overflow-x-auto rounded-lg border border-slate-200">
            <table class="min-w-[900px] divide-y divide-slate-200 text-left text-sm">
                <thead class="bg-slate-50 font-black text-slate-700">
                    <tr>
                        <th class="px-4 py-3 align-top"><strong>Case</strong></th>
                        <th class="px-4 py-3 align-top"><strong>Main site</strong></th>
                        <th class="px-4 py-3 align-top"><strong>Term / mode</strong></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 bg-white">
                    <tr>
                        <td class="px-4 py-3 align-top font-black text-slate-900"><strong>Ordinary review</strong></td>
                        <td class="px-4 py-3 align-top"><strong>Full refund</strong></td>
                        <td class="px-4 py-3 align-top"><strong>Within 14 days of the purchase conclusion; no motivation</strong></td>
                    </tr>
                    <tr>
                        <td class="px-4 py-3 align-top font-black text-slate-900"><strong>Review by 31 December 2026</strong></td>
                        <td class="px-4 py-3 align-top"><strong>No automatic refund</strong></td>
                        <td class="px-4 py-3 align-top"><strong>The pass keeps 12 months from proper individual activation</strong></td>
                    </tr>
                    <tr>
                        <td class="px-4 py-3 align-top font-black text-slate-900"><strong>No go-lives by 31 December 2026</strong></td>
                        <td class="px-4 py-3 align-top"><strong>Choice of maintenance and full refund</strong></td>
                        <td class="px-4 py-3 align-top"><strong>Request in the term indicated in the communication, not less than 30 days</strong></td>
                    </tr>
                    <tr>
                        <td class="px-4 py-3 align-top font-black text-slate-900"><strong>Lack of final launch</strong></td>
                        <td class="px-4 py-3 align-top"><strong>Full refund</strong></td>
                        <td class="px-4 py-3 align-top"><strong>Disposed by WAYOUT without user choice</strong></td>
                    </tr>
                    <tr>
                        <td class="px-4 py-3 align-top font-black text-slate-900"><strong>Substantial and worsening change before go-live</strong></td>
                        <td class="px-4 py-3 align-top"><strong>Choice of full acceptance and refund</strong></td>
                        <td class="px-4 py-3 align-top"><strong>Within 30 days of communication</strong></td>
                    </tr>
                    <tr>
                        <td class="px-4 py-3 align-top font-black text-slate-900"><strong>Final activation impairment not attributable to the user</strong></td>
                        <td class="px-4 py-3 align-top"><strong>Full refund</strong></td>
                        <td class="px-4 py-3 align-top"><strong>After assistance and technical verification</strong></td>
                    </tr>
                    <tr>
                        <td class="px-4 py-3 align-top font-black text-slate-900"><strong>Duplicated payment, overbooking or system error</strong></td>
                        <td class="px-4 py-3 align-top"><strong>Refund of the amount not due</strong></td>
                        <td class="px-4 py-3 align-top"><strong>After order verification/payment</strong></td>
                    </tr>
                    <tr>
                        <td class="px-4 py-3 align-top font-black text-slate-900"><strong>Compliance effect after go-live</strong></td>
                        <td class="px-4 py-3 align-top"><strong>Restoration; in cases of law reduction or resolution/refund</strong></td>
                        <td class="px-4 py-3 align-top"><strong>According to the discipline of digital services</strong></td>
                    </tr>
                    <tr>
                        <td class="px-4 py-3 align-top font-black text-slate-900"><strong>Missed voluntary use or unsatisfactory social result</strong></td>
                        <td class="px-4 py-3 align-top"><strong>No refund per se</strong></td>
                        <td class="px-4 py-3 align-top"><strong>Save undue rights</strong></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </section>

    <section>
        <h2 class="text-2xl font-black text-slate-950">4. Right of withdrawal within 14 days</h2>
        <p class="mt-3"><strong>4.1 </strong>The buyer acting as a consumer can withdraw from the purchase of the Founder Pass without indicating the reason within 14 calendar days from the conclusion of the contract, that is from the confirmation of the order following the completed payment.</p>
        <p class="mt-3"><strong>4.2 </strong>To comply with the deadline it is sufficient to pass the withdrawal statement before its expiry. If the information on the right of withdrawal has not been correctly provided, the extensions of the term provided for in the current legislation apply.</p>
        <p class="mt-3"><strong>4.3 </strong>Since the Founder Pass is sold in pre-sale and can only be activated by the go-live, with a duration of 12 months from the correct individual activation, WAYOUT recognizes the full refund for the timely withdrawal even if the go-live or activation intervene during the 14-day period, without holding proportional amounts.</p>
    </section>

    <section>
        <h2 class="text-2xl font-black text-slate-950">5. “Withdraw from the contract here” online function</h2>
        <p class="mt-3"><strong>5.1 </strong>During the entire period in which the right of withdrawal can be exercised, the site provides a visible and easily accessible function called “Withdraw from the contract here”, available at the address <a class="font-black text-violet-700" href="https://wayoutapp.it/recedere-dal-contratto">https://wayoutapp.it/recedere-dal-contratto</a>.</p>
        <p class="mt-3"><strong>5.2 </strong>The function allows you to provide or confirm at least: name and surname; email used for purchase; useful information to identify the contract or order; electronic contact to which to send confirmation.</p>
        <p class="mt-3"><strong>5.3 </strong>After filling out, the user definitively submits the statement using the “Confirm withdrawal” command or other formula equally unequivocal.</p>
        <p class="mt-3"><strong>5.4 </strong>WAYOUT sends without undue delay a reception notice on durable medium, containing the transmitted statement and the date and time of sending. The receipt proves the correct acquisition of the request, without limiting the other means by which the consumer can prove to have exercised the right in terms.</p>
    </section>

    <section>
        <h2 class="text-2xl font-black text-slate-950">6. Alternative channels to exercise withdrawal</h2>
        <p class="mt-3"><strong>6.1 </strong>The consumer can also exercise the withdrawal by sending an explicit statement to <a class="font-black text-violet-700" href="mailto:amministrazione@wayoutapp.it">amministrazione@wayoutapp.it</a> or the PEC <a class="font-black text-violet-700" href="mailto:wayout@pec.wayoutapp.it">wayout@pec.wayoutapp.it</a>. You can use the model in Annex A, but you do not have to do so.</p>
        <p class="mt-3"><strong>6.2 </strong>Communication needs to identify the consumer and purchase. It is recommended to indicate: name and surname, purchase email, order number, date of purchase and Founder Pass purchased.</p>
        <p class="mt-3"><strong>6.3 </strong>The reason for the withdrawal is optional and does not affect its validity.</p>
    </section>

    <section>
        <h2 class="text-2xl font-black text-slate-950">7. Effects of withdrawal and refund mode</h2>
        <p class="mt-3"><strong>7.1 </strong>The withdrawal terminates the contract relating to Founder Pass. If the pass has already been associated with an account, WAYOUT can disable it as a result of the refund.</p>
        <p class="mt-3"><strong>7.2 </strong>WAYOUT refunds all payments received for purchase, without undue delay and however within 14 days from the day on which the decision to withdraw is informed.</p>
        <p class="mt-3"><strong>7.3 </strong>The refund is made using the same means of payment used for the initial transaction, unless otherwise agreed with the consumer and provided that the latter does not support costs. WAYOUT does not apply fees for refund.</p>
        <p class="mt-3"><strong>7.4 </strong>If the refund on the original medium is technically impossible, WAYOUT contactes the buyer to agree on a safe alternative medium after the necessary checks. The times with which the bank or circuit makes the accreditation visible can depend on third parties and do not change the time within which WAYOUT must have the refund.</p>
    </section>

    <section>
        <h2 class="text-2xl font-black text-slate-950">8. Refund if the go-live does not happen by 31 December 2026</h2>
        <p class="mt-3"><strong>8.1 </strong>If the app is not publicly available in the pilot city of Milan by 31 December 2026, WAYOUT informs buyers on durable medium.</p>
        <p class="mt-3"><strong>8.2 </strong>The buyer can choose whether to keep the Founder Pass for the next go-live or request full refund. The communication indicates a window to exercise the choice, in any case not less than 30 days from reception.</p>
        <p class="mt-3"><strong>8.3 </strong>The Founder Pass remains valid and can be activated by the next go-live. The duration of 12 months will decorate from the correct individual activation, without automatic renewal. The mandatory rights applicable may remain firm.</p>
        <p class="mt-3"><strong>8.4 </strong>The simple postponement of the launch by 31 December 2026 does not entitle itself to reimbursement, unless the timely withdrawal, a different commercial decision of WAYOUT or other remedies provided by law.</p>
    </section>

    <section>
        <h2 class="text-2xl font-black text-slate-950">9. Lack of final launch</h2>
        <p class="mt-3"><strong>9.1 </strong>If WAYOUT finally decides not to launch the app or not to make the pre-sale service available, the Founder Pass contract is resolved and the buyer is entitled to full refund.</p>
        <p class="mt-3"><strong>9.2 </strong>In this case, WAYOUT will communicate the decision and issue the refund without requiring a prior request from the buyer, as a rule to the original payment method, without fees and within 14 days of the decision or notice.</p>
        <p class="mt-3"><strong>9.3 </strong>The refund extinguishes the right to activate the Founder Pass, without prejudice to the additional rights recognised by law.</p>
    </section>

    <section>
        <h2 class="text-2xl font-black text-slate-950">10. Substantial and worsening change before go-live</h2>
        <p class="mt-3"><strong>10.1 </strong>Not every update, technical modification, graphics, organizational or security entitles you to refund. The remedy applies when, before the go-live, a change eliminates or substantially reduces and worsens an essential feature of the plan purchased.</p>
        <p class="mt-3"><strong>10.2 </strong>WAYOUT informs the buyer on durable medium, describes the change and allows you to choose between accepting the modified service or request full refund within 30 days of the communication.</p>
        <p class="mt-3"><strong>10.3 </strong>The Founder Pass remains valid and is activated under the conditions communicated. This does not imply renunciation of consumer mandatory rights.</p>
    </section>

    <section>
        <h2 class="text-2xl font-black text-slate-950">11. Activation and technical problems</h2>
        <p class="mt-3"><strong>11.1 </strong>If activation is prevented from delays, errors or malfunctions attributable to WAYOUT, the Company provides assistance and adopts an appropriate remedy, such as recovery, reactivation, extension of the period or other equivalent measure.</p>
        <p class="mt-3"><strong>11.2 </strong>If the activation remains permanently impossible for reasons not attributable to the buyer, WAYOUT fully refunds the price paid, without prejudice to the additional remedies provided for by the discipline of digital services.</p>
        <p class="mt-3"><strong>11.3 </strong>If the completion of the onboarding takes place at a time after the go-live by choice of the buyer, the duration of the Founder Pass is not reduced: the 12 months are due to the proper individual activation. The simple voluntary delay in activation does not in itself generate a further right to reimbursement.</p>
    </section>

    <section>
        <h2 class="text-2xl font-black text-slate-950">12. Order or payment errors</h2>
        <p class="mt-3"><strong>12.1 </strong>In the event of a duplicate payment, an amount charged but not due, an order exceeding availability, overbooking, a technical error or definitive rejection of the order after the charge, WAYOUT cancels the amount not due and refunds it in full without charge.</p>
        <p class="mt-3"><strong>12.2 </strong>WAYOUT may request the information strictly necessary to reconcile order and payment, including order number, purchase email, date, amount and debit proof. It is not necessary to transmit the complete data of the card.</p>
    </section>

    <section>
        <h2 class="text-2xl font-black text-slate-950">13. Conformity defects and remedies after go-live</h2>
        <p class="mt-3"><strong>13.1 </strong>From go-live the digital service is subject to the legal guarantee of conformity provided by the Consumer Code. In the event of failure to supply or lack of conformity, the consumer may require priority to restore compliance without expenses and without significant drawbacks, within a reasonable period.</p>
        <p class="mt-3"><strong>13.2 </strong>When the legal requirements apply, the consumer can obtain a proportional reduction in the price or termination of the contract. In the event of termination of a service provided for a period, the refund may cover the period of non-compliance and the part of the price anticipated for the period remaining unused.</p>
        <p class="mt-3"><strong>13.3 </strong>The refunds due for price reduction or resolution are made without undue delay and in any case within 14 days from when WAYOUT is informed of the consumer's decision, using the same means of payment unless otherwise agreed at no cost.</p>
        <p class="mt-3"><strong>13.4 </strong>Changes in the digital service after go-live are also subject to the mandatory rules that allow the consumer, in the foreseen cases, to withdraw free within 30 days when the change affects negatively and in a way not negligible on use or access to the service.</p>
    </section>

    <section>
        <h2 class="text-2xl font-black text-slate-950">14. Suspension, ban and revision of the measure</h2>
        <p class="mt-3"><strong>14.1 </strong>The suspension or closure of the account correctly arranged for serious or repeated violations of the Terms of the app, the Community Guidelines, the Safety Policy or the law may result in loss of residual access without refund, within the limits permitted by law and according to a proportional assessment of the concrete case.</p>
        <p class="mt-3"><strong>14.2 </strong>The user can contest the measurement through the channels indicated in the received communication. If the suspension or ban is incorrect, WAYOUT adopts an appropriate remedy, such as reactivation, extension of the pass or proportional refund; if the service cannot be restored, the relevant mandatory remedies apply.</p>
    </section>

    <section>
        <h2 class="text-2xl font-black text-slate-950">15. Cases that do not normally give right to reimbursement</h2>
        <p class="mt-3"><strong>15.1 </strong>Without prejudice to the withdrawal, legal guarantee and other mandatory rights, they do not give themselves the right to reimbursement:</p>
        <ul class="mt-3 list-disc space-y-2 pl-6">
            <li>lack of voluntary use of Founder Pass or late registration attributable to the user;</li>
            <li>no participation, no acceptance or cancellation of a specific table;</li>
            <li>lack of desired social result, incompatibility or subjective dissatisfaction;</li>
            <li>number of users, tables, creators or social opportunities below expectations;</li>
            <li>absence of a specific local, event, evening, entrance, physical table, drink or third party service;</li>
            <li>decisions or conditions applied independently by locals, creators, users or third parties;</li>
            <li>delay of the go-live remaining by 31 December 2026;</li>
            <li>suspension or ban legitimately attributable to user violations.</li>
        </ul>
        <p class="mt-3"><strong>15.2 </strong>The Founder Pass is a timeless digital subscription and not a consumer credit, an entrance fee, a local reservation or a guarantee of availability of events or social results.</p>
    </section>

    <section>
        <h2 class="text-2xl font-black text-slate-950">16. How to submit a refund request other than withdrawal</h2>
        <p class="mt-3"><strong>16.1 </strong>The request can be sent via the online function “Withdraw from the contract here” available at the address <a class="font-black text-violet-700" href="https://wayoutapp.it/recedere-dal-contratto">https://wayoutapp.it/recedere-dal-contratto</a>, or <a class="font-black text-violet-700" href="mailto:amministrazione@wayoutapp.it">amministrazione@wayoutapp.it</a>. It must indicate, as available: name, surname, purchase email, order number, date of purchase, Founder Pass purchased and category of request.</p>
        <p class="mt-3"><strong>16.2 </strong>For ordinary withdrawal no motivation is required. For other cases, a brief description and any useful documents are optional, but can accelerate verification. WAYOUT does not require excess data or full payment card data.</p>
        <p class="mt-3"><strong>16.3 </strong>WAYOUT confirms the reception and can ask for strictly necessary integrations. Refunds other than withdrawal are processed, as a rule, within 14 days of acceptance of the request or communication that determines the right to reimbursement, except for more favourable terms.</p>
    </section>

    <section>
        <h2 class="text-2xl font-black text-slate-950">17. Data protection</h2>
        <p class="mt-3"><strong>17.1 </strong>The data provided to manage withdrawal, refund requests, checks, payments and disputes are processed by WAYOUT according to the <a class="font-black text-violet-700" href="/privacy-policy">Privacy Policy</a> of the site, to perform the contract, fulfill the legal obligations, prevent fraud and protect the rights of the parties.</p>
        <p class="mt-3"><strong>17.2 </strong>Refund management may require the involvement of Stripe, payment institutions, banks, technical suppliers and professionals in charge, within the limits necessary to their respective activities.</p>
    </section>

    <section>
        <h2 class="text-2xl font-black text-slate-950">18. Coordination with other documents and rights</h2>
        <p class="mt-3"><strong>18.1 </strong>This <a class="font-black text-violet-700" href="/recedere-dal-contratto">Refund Policy</a> supplements the <a class="font-black text-violet-700" href="/termini-di-vendita">Online Sales Terms</a> and the <a class="font-black text-violet-700" href="/condizioni-di-pre-sale">Specific Pre-sale Terms</a>. If they conflict, applicable mandatory provisions prevail; among the contractual documents, the provisions most specific to the particular case apply, interpreted consistently with consumer rights.</p>
        <p class="mt-3"><strong>18.2 </strong>No provision of this policy limits the right of withdrawal, the legal guarantee of conformity, remedies for failure to supply or other rights that the law recognizes unquestionably to the consumer.</p>
    </section>

    <section>
        <h2 class="text-2xl font-black text-slate-950">Annex A — Model withdrawal form</h2>
        <div class="mt-4 space-y-3 rounded-[1.25rem] border-2 border-amber-300 bg-white p-5">
            <p>Complete and return this form only if you wish to withdraw from the contract. You may also use the online function or any other unequivocal statement.</p>
            <p><strong>Addressee:</strong> WAYOUT S.r.l., Via Guglielmo Marconi 24/B, 20082 Binasco (MI), Italy — <a class="font-black text-violet-700" href="mailto:amministrazione@wayoutapp.it">amministrazione@wayoutapp.it</a> — PEC <a class="font-black text-violet-700" href="mailto:wayout@pec.wayoutapp.it">wayout@pec.wayoutapp.it</a></p>
            <p>I hereby give notice that I withdraw from the contract relating to the purchase of the following WAYOUT Founder Pass:</p>
            <div class="grid gap-4 pt-2">
                <p><span class="font-bold">Founder Pass:</span><span class="mt-2 block min-h-7 border-b border-dashed border-slate-500"></span></p>
                <p><span class="font-bold">Order number (if available):</span><span class="mt-2 block min-h-7 border-b border-dashed border-slate-500"></span></p>
                <p><span class="font-bold">Purchase date:</span><span class="mt-2 block min-h-7 border-b border-dashed border-slate-500"></span></p>
                <p><span class="font-bold">Email used for the purchase:</span><span class="mt-2 block min-h-7 border-b border-dashed border-slate-500"></span></p>
                <p><span class="font-bold">Consumer’s name:</span><span class="mt-2 block min-h-7 border-b border-dashed border-slate-500"></span></p>
                <p><span class="font-bold">Address (optional):</span><span class="mt-2 block min-h-7 border-b border-dashed border-slate-500"></span></p>
                <div class="grid gap-4 sm:grid-cols-2">
                    <p><span class="font-bold">Date:</span><span class="mt-2 block min-h-7 border-b border-dashed border-slate-500"></span></p>
                    <p><span class="font-bold">Signature (paper forms only):</span><span class="mt-2 block min-h-7 border-b border-dashed border-slate-500"></span></p>
                </div>
            </div>
            <a class="inline-flex rounded-xl bg-slate-950 px-5 py-3 font-black text-white hover:bg-violet-800" href="/documenti/modulo-tipo-recesso?lang=en">Download the form (.docx)</a>
        </div>
    </section>
</div>
@endsection
