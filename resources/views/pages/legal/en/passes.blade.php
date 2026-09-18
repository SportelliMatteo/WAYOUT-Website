@extends('pages.legal.layout', [
    'title' => 'Come funzionano i Pass',
    'description' => 'Waitlist Pass, Founder Join 12M e Founder Creator 12M: cosa includono, quando si attivano e quali condizioni si applicano.',
])

@section('legal-content')
<div class="space-y-8">
    <div class="mt-4 space-y-2 rounded-lg border border-violet-200 bg-violet-50/60 p-4">
        <p class="font-black text-slate-950"><strong>In short.</strong></p>
        <p class="text-slate-700">The entry to the waitlist is free and does not entail purchasing obligations. The Waitlist Pass is a potential free 60-day benefit with Join and Creator features. The Founder Pass instead are digital subscriptions purchased in pre-sale, valid for 12 months from the correct individual activation, possible by the public go-live of the app and without automatic renewal.</p>
    </div>
    <section>
        <h2 class="text-2xl font-black text-slate-950">1. The Three Pass Comparison</h2>
        <div class="mt-4 grid overflow-hidden rounded-lg border border-slate-200 bg-white md:grid-cols-[0.38fr_0.62fr]">
            <div class="space-y-2 bg-slate-950 p-5 text-white">
                <p><strong>WAITLIST PASS</strong></p>
                <p><strong>Free</strong></p>
                <p>60 days</p>
            </div>
            <div class="space-y-2 p-5 text-slate-700">
                <p>Possible benefit for the first 2,000 valid users of the waitlist.</p>
                <p><em><strong>Includes: </strong></em>Join and Creator features · Participation requests · Chat after acceptance · Creating and managing tables and their participation requests.</p>
                <p><em><strong>Not included: </strong></em>Services, reservations, admission, drinks or other services provided by venues or third parties · Guaranteed acceptance or availability of specific tables/events.</p>
            </div>
        </div>
        <div class="mt-4 grid overflow-hidden rounded-lg border border-slate-200 bg-white md:grid-cols-[0.38fr_0.62fr]">
            <div class="space-y-2 bg-slate-950 p-5 text-white">
                <p><strong>FOUNDER JOIN 12M</strong></p>
                <p><strong>€29 VAT included</strong></p>
                <p>12 months from the correct individual activation, possible from the go-live</p>
            </div>
            <div class="space-y-2 p-5 text-slate-700">
                <p>Pre-launch subscription to use Join features.</p>
                <p><em><strong>Includes: </strong></em>Discovery of tables and social occasions · Participation requests · Chat of accepted tables</p>
                <p><em><strong>Not included: </strong></em>Creating and managing tables · Functionality Creator</p>
            </div>
        </div>
        <div class="mt-4 grid overflow-hidden rounded-lg border border-slate-200 bg-white md:grid-cols-[0.38fr_0.62fr]">
            <div class="space-y-2 bg-slate-950 p-5 text-white">
                <p><strong>FOUNDER CREATOR 12M</strong></p>
                <p><strong>€59 VAT included</strong></p>
                <p>12 months from the correct individual activation, possible from the go-live</p>
            </div>
            <div class="space-y-2 p-5 text-slate-700">
                <p>Subscription pre-launch with Join and Creator functions.</p>
                <p><em><strong>Includes: </strong></em>All Join features · Creating and managing digital tables · Managing requests for participation</p>
                <p><em><strong>Not included: </strong></em>Unlimited right to create tables · Services, reservations or entrances</p>
            </div>
        </div>
        <h3 class="mt-6 text-xl font-black text-slate-900">Essential comparison</h3>
        <div class="mt-4 overflow-x-auto rounded-lg border border-slate-200">
            <table class="min-w-[900px] divide-y divide-slate-200 text-left text-sm">
                <thead class="bg-slate-50 font-black text-slate-700">
                    <tr>
                        <th class="px-4 py-3 align-top"><strong>Feature</strong></th>
                        <th class="px-4 py-3 align-top"><strong>Waitlist Pass</strong></th>
                        <th class="px-4 py-3 align-top"><strong>Founder Join 12M</strong></th>
                        <th class="px-4 py-3 align-top"><strong>Founder Creator 12M</strong></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 bg-white">
                    <tr>
                        <td class="px-4 py-3 align-top font-black text-slate-900"><em><strong>Price</strong></em></td>
                        <td class="px-4 py-3 align-top">Free</td>
                        <td class="px-4 py-3 align-top">€29</td>
                        <td class="px-4 py-3 align-top">€59</td>
                    </tr>
                    <tr>
                        <td class="px-4 py-3 align-top font-black text-slate-900"><em><strong>VAT price included</strong></em></td>
                        <td class="px-4 py-3 align-top">N/A</td>
                        <td class="px-4 py-3 align-top">Yes</td>
                        <td class="px-4 py-3 align-top">Yes</td>
                    </tr>
                    <tr>
                        <td class="px-4 py-3 align-top font-black text-slate-900"><em><strong>Duration</strong></em></td>
                        <td class="px-4 py-3 align-top">60 days from individual activation</td>
                        <td class="px-4 py-3 align-top">12 months from the correct individual activation, possible from the go-live</td>
                        <td class="px-4 py-3 align-top">12 months from the correct individual activation, possible from the go-live</td>
                    </tr>
                    <tr>
                        <td class="px-4 py-3 align-top font-black text-slate-900"><em><strong>Functions Join</strong></em></td>
                        <td class="px-4 py-3 align-top">Yes</td>
                        <td class="px-4 py-3 align-top">Yes</td>
                        <td class="px-4 py-3 align-top">Yes</td>
                    </tr>
                    <tr>
                        <td class="px-4 py-3 align-top font-black text-slate-900"><em><strong>Functions Creator</strong></em></td>
                        <td class="px-4 py-3 align-top">Yes</td>
                        <td class="px-4 py-3 align-top">No.</td>
                        <td class="px-4 py-3 align-top">Yes</td>
                    </tr>
                    <tr>
                        <td class="px-4 py-3 align-top font-black text-slate-900"><em><strong>Automatic renewal</strong></em></td>
                        <td class="px-4 py-3 align-top">No.</td>
                        <td class="px-4 py-3 align-top">No.</td>
                        <td class="px-4 py-3 align-top">No.</td>
                    </tr>
                    <tr>
                        <td class="px-4 py-3 align-top font-black text-slate-900"><em><strong>Posts / availability</strong></em></td>
                        <td class="px-4 py-3 align-top">First 2,000 valid users</td>
                        <td class="px-4 py-3 align-top">Maximum 500 passes</td>
                        <td class="px-4 py-3 align-top">Maximum 150 passes</td>
                    </tr>
                    <tr>
                        <td class="px-4 py-3 align-top font-black text-slate-900"><em><strong>Early access to the app</strong></em></td>
                        <td class="px-4 py-3 align-top">No.</td>
                        <td class="px-4 py-3 align-top">No.</td>
                        <td class="px-4 py-3 align-top">No.</td>
                    </tr>
                    <tr>
                        <td class="px-4 py-3 align-top font-black text-slate-900"><em><strong>Cumulable with other Pass</strong></em></td>
                        <td class="px-4 py-3 align-top">No, except for express communication</td>
                        <td class="px-4 py-3 align-top">No.</td>
                        <td class="px-4 py-3 align-top">No.</td>
                    </tr>
                </tbody>
            </table>
        </div>
        <p class="mt-3">The availability of Founder Pass is consolidated only with the completed payment and the order confirmation. The pre-sale may close before the quantitative caps in cases provided for in the applicable conditions.</p>
    </section>

    <section>
        <h2 class="text-2xl font-black text-slate-950">2. Waitlist and Waitlist Pass free</h2>
        <p class="mt-3">Entering the waitlist means showing your interest free of charge for the launch of WAYOUT. Registration does not constitute a purchase, does not involve charges and does not oblige to purchase a Founder Pass.</p>
        <h3 class="mt-6 text-xl font-black text-slate-900">Who can receive the Waitlist Pass</h3>
        <p class="mt-3">The Waitlist Pass is a possible promotional benefit reserved for the first 2,000 valid users of the waitlist. The registration itself does not automatically guarantee the benefit.</p>
        <ul class="mt-3 list-disc space-y-2 pl-6">
            <li>You must be at least 18 years old.</li>
            <li>The profile must be completed with true and usable data.</li>
            <li>Duplicate, abusive, fraudulent or automated inscriptions must not be found.</li>
            <li>The fulfilments and verifications communicated by WAYOUT must be completed.</li>
        </ul>
        <h3 class="mt-6 text-xl font-black text-slate-900">Duration and activation</h3>
        <p class="mt-3"><em><strong>Duration: </strong></em>60 days from the date when the individual user successfully completes registration in the app and activates the benefit. The duration does not automatically decorate from the general go-live.</p>
        <p class="mt-3"><em><strong>Activation time: </strong></em>activation must be completed within 30 calendar days from the email with which WAYOUT communicates that the benefit is available, unless a longer term indicated in the same communication.</p>
        <div class="mt-4 space-y-2 rounded-lg border border-violet-200 bg-violet-50/60 p-4">
            <p class="font-black text-slate-950"><em><strong>Careful. </strong></em></p>
            <p class="text-slate-700">If the activation is not completed within the time limit, the benefit is deemed waived and WAYOUT can assign its place to another valid user. Any technical problems attributable to WAYOUT will be handled in appropriate ways and terms.</p>
        </div>
        <h3 class="mt-6 text-xl font-black text-slate-900">What includes</h3>
        <ul class="mt-3 list-disc space-y-2 pl-6">
            <li>All Join and Creator features made available in the app.</li>
            <li>Discovery, sending participation requests and chatting after acceptance.</li>
            <li>Creating and managing digital tables and their participation requests, always subject to the rules and limits of the service.</li>
        </ul>
        <h3 class="mt-6 text-xl font-black text-slate-900">What does not include</h3>
        <ul class="mt-3 list-disc space-y-2 pl-6">
            <li>Admission to venues, reservations, physical tables, tickets, drinks, food or third-party services.</li>
            <li>Guaranteed acceptance at a specific table.</li>
            <li>An absolute or unlimited right to create tables, which remain subject to publication, capacity, frequency, fair use, security, moderation and community rules.</li>
        </ul>
        <p class="mt-3"><em><strong>Personal features. </strong></em>The Waitlist Pass is personal, non-transferable, non-resible and free of monetary value. It cannot be converted into money, credit or services of third-party premises.</p>
    </section>

    <section>
        <h2 class="text-2xl font-black text-slate-950">3. Founder Pass purchased in pre-sale</h2>
        <p class="mt-3">Founder Pass allows you to purchase a WAYOUT digital subscription before launch at the founder price. Payment is unique and anticipated, but the use of features starts only from the public go-live of the app.</p>
        <div class="mt-4 space-y-2 rounded-lg border border-violet-200 bg-violet-50/60 p-4">
            <p class="font-black text-slate-950"><em><strong>The pre-sale is not early access. </strong></em></p>
            <p class="text-slate-700">The purchase does not give access to the app before launch, to a public beta, to events, entrances or physical services. It only blocks the Founder Pass that will be activated by the go-live and will start from the proper individual activation.</p>
        </div>
        <h3 class="mt-6 text-xl font-black text-slate-900">Founder Join 12M</h3>
        <p class="mt-3"><em><strong>Total price: </strong></em>€29, VAT and tax charges applicable included. <em><strong>Maximum availability: </strong></em>500 passes.</p>
        <p class="mt-3">Founder Join 12M allows for 12 months from the correct individual activation, possible from the go-live the Join features made available in the app, including the discovery of tables or social occasions, the sending of requests for participation and access to the chat tables for which the request is accepted.</p>
        <p class="mt-3"><em><strong>No guarantee </strong></em>acceptance in a specific table, the presence of tables on each date or place, the entrance to a certain evening or local, or a minimum number of users or social occasions. It does not include Creator functions.</p>
        <h3 class="mt-6 text-xl font-black text-slate-900">Founder Creator 12M</h3>
        <p class="mt-3"><em><strong>Total price: </strong></em>€59, VAT and tax charges applicable included. <em><strong>Maximum availability: </strong></em>150 passes.</p>
        <p class="mt-3">Founder Creator 12M includes all Join features and features to create and manage digital tables or social experiences and their requests, according to the rules and limits of the service.</p>
        <div class="mt-4 space-y-2 rounded-lg border border-violet-200 bg-violet-50/60 p-4">
            <p class="font-black text-slate-950"><em><strong>No additional discretionary selection. </strong></em></p>
            <p class="text-slate-700">Those who purchase Founder Creator 12M must not exceed an additional discretionary selection process. For activation it must complete the normal account requirements: registration, verification of the phone number and age, true data, real and conform personal photography and acceptance of the applicable terms and rules.</p>
        </div>
        <p class="mt-3"><em><strong>Limits of Creator functions. </strong></em>The purchase does not give an absolute or unlimited right to create any table, anywhere, date or number. Creator functions remain subject to limits of publication, capacity, frequency, overlap, fair use, moderation, security, technical availability and community rules, without emptying the essential content of the plan purchased.</p>
    </section>

    <section>
        <h2 class="text-2xl font-black text-slate-950">4. When they leave and how long</h2>
        <h3 class="mt-6 text-xl font-black text-slate-900">Founder Pass</h3>
        <ul class="mt-3 list-disc space-y-2 pl-6">
            <li>They can be activated by the public go-live of the app in the pilot city of Milan; the 12 months run from the correct individual activation of the individual user, not from the date of purchase and not automatically from the go-live.</li>
            <li>It lasts 12 months.</li>
            <li>They end without automatic renewal and without additional charges.</li>
            <li>Upon expiry, you can choose freely if you buy an ordinary plan available.</li>
            <li>If you complete the registration or start using the app late for your choice, the duration only decorates from the correct individual activation.</li>
        </ul>
        <h3 class="mt-6 text-xl font-black text-slate-900">Waitlist Pass</h3>
        <ul class="mt-3 list-disc space-y-2 pl-6">
            <li>It lasts 60 days from proper individual activation.</li>
            <li>It must be activated within the period communicated by WAYOUT, as a rule 30 days from the availability email.</li>
            <li>It does not automatically renew and does not generate objections.</li>
        </ul>
    </section>

    <section>
        <h2 class="text-2xl font-black text-slate-950">5. Personal and non cumulative passes</h2>
        <p class="mt-3">Passes are personal and intended to be associated with your account. They may not be transferred, transferred or reselled, except for the management of material errors or addresses that are no longer accessible according to WAYOUT checks.</p>
        <p class="mt-3"><em><strong>Not cumulative rule. </strong></em>The Founder Pass does not sum up to the 60-day free Waitlist Pass, any Launch Offer or other incompatible promotions, unless otherwise express WAYOUT communication. Those who buy a Founder Pass will use the plan purchased from the go-live.</p>
    </section>

    <section>
        <h2 class="text-2xl font-black text-slate-950">6. What about the Pass</h2>
        <div class="mt-4 space-y-2 rounded-lg border border-violet-200 bg-violet-50/60 p-4">
            <p class="font-black text-slate-950"><em><strong>The Pass is WAYOUT, not the local. </strong></em></p>
            <p class="text-slate-700">Passes give access to the digital functionality of the platform. These are not tickets, local bookings, event packages, credits to spend or guarantees to obtain specific social results.</p>
        </div>
        <h3 class="mt-6 text-xl font-black text-slate-900">The Pass does not include and does not guarantee</h3>
        <ul class="mt-3 list-disc space-y-2 pl-6">
            <li>entrances, lists, tickets, reservations or admission to locals, clubs or events;</li>
            <li>physical tables, bottles, drinks, drinks, catering, wardrobe or transportation;</li>
            <li>prices, conditions, availability or quality of services offered by the premises;</li>
            <li>the presence of a minimum number of users, creator, tables or evenings;</li>
            <li>acceptance in specific tables or actual participation in certain events;</li>
            <li>affinity, personal compatibility, friendship, relationship, mutual appreciation or positive outcome of the meeting;</li>
            <li>the absence of cancellations, no-shows, incorrect conduct, illicit or risks related to interactions between people.</li>
        </ul>
        <p class="mt-3">Any costs, bookings or relationships with locals, creators, users or other third parties are separated from the Pass and are governed by the agreements directly concluded between the parties concerned, unless a specific official partnership expressly indicated by WAYOUT.</p>
    </section>

    <section>
        <h2 class="text-2xl font-black text-slate-950">7. Milan launch and geographic availability</h2>
        <p class="mt-3">The initial operation is planned in the pilot city of Milan. In the first phase, tables, creators, users and social opportunities will be concentrated mainly on Milan.</p>
        <p class="mt-3">The extension to other Italian cities may take place progressively, but is not guaranteed within specific dates. Simple technical access to the app from other territories does not guarantee the presence of a community or social opportunities in the user’s location.</p>
    </section>

    <section>
        <h2 class="text-2xl font-black text-slate-950">8. Purchase, launch, withdrawal and refunds</h2>
        <h3 class="mt-6 text-xl font-black text-slate-900">Purchase and availability</h3>
        <ul class="mt-3 list-disc space-y-2 pl-6">
            <li>The pre-sale is reserved for senior individuals enrolled in the WAYOUT waitlist and enabled to access the purchase.</li>
            <li>The prices of €29 and €59 are final and include VAT and tax charges applicable.</li>
            <li>The availability of the Pass is consolidated only after payment and confirmation of the order.</li>
            <li>The pre-sale may close in advance to reach the caps or in other cases provided under the applicable conditions.</li>
        </ul>
        <h3 class="mt-6 text-xl font-black text-slate-900">Right of withdrawal</h3>
        <p class="mt-3">The consumer may exercise the right of withdrawal within 14 calendar days from the conclusion of the purchase, without indicating the reason, in accordance with the manner described in the page <a class="font-black text-violet-700" href="/recedere-dal-contratto">Withdrawal and refunds</a> and in contractual documents.</p>
        <h3 class="mt-6 text-xl font-black text-slate-900">If the sled launch</h3>
        <p class="mt-3">The ordinary date for launch is indicative. If the go-live is postponed but takes place by 31 December 2026, the Founder Pass will be activated by the actual go-live and will still last 12 months from the correct individual activation. The simple reference within this date does not mean, in itself, an automatic refund, without prejudice to the timely withdrawal and other applicable safeguards.</p>
        <h3 class="mt-6 text-xl font-black text-slate-900">If the app does not go live by 31 December 2026</h3>
        <p class="mt-3">WAYOUT will inform buyers and allow you to choose, within a window not less than 30 days from the communication, if you keep the Founder Pass for the next go-live or request full refund. In the absence of request within the communicated window, the Pass will remain valid for the next launch, without prejudice to the mandatory rights.</p>
        <h3 class="mt-6 text-xl font-black text-slate-900">If the launch is finally cancelled</h3>
        <p class="mt-3">If a final decision is made not to launch the app or not to make the pre-sale service available, WAYOUT will issue a full refund, as a rule to the original payment method and without requiring a prior request.</p>
        <h3 class="mt-6 text-xl font-black text-slate-900">Lack of use and violations</h3>
        <p class="mt-3">The lack of voluntary use of the Pass, the lack of acceptance in specific tables, a community less than expectations or the absence of a given social result do not give itself the right to reimbursement. Limitations or suspensions due to violations of the rules are governed by the Terms of the app; any errors recognized will be managed with a proportional remedy.</p>
    </section>

    <section>
        <h2 class="text-2xl font-black text-slate-950">9. Frequently asked questions</h2>
        <div class="mt-4 space-y-2 rounded-lg border border-violet-200 bg-violet-50/60 p-4">
            <p class="font-black text-slate-950">Is the waitlist registered for a fee?</p>
            <p class="text-slate-700">No. It is free, does not involve charges and does not oblige to buy a Founder Pass.</p>
        </div>
        <div class="mt-4 space-y-2 rounded-lg border border-violet-200 bg-violet-50/60 p-4">
            <p class="font-black text-slate-950">Being in the waitlist guarantees the Waitlist Pass?</p>
            <p class="text-slate-700">No. The benefit is reserved for the first 2,000 valid users who meet the requirements and complete the activation within the communicated deadline.</p>
        </div>
        <div class="mt-4 space-y-2 rounded-lg border border-violet-200 bg-violet-50/60 p-4">
            <p class="font-black text-slate-950">Since when are the 60 days of the Waitlist Pass?</p>
            <p class="text-slate-700">From the correct individual activation in the app, not automatically from the general date of go-live.</p>
        </div>
        <div class="mt-4 space-y-2 rounded-lg border border-violet-200 bg-violet-50/60 p-4">
            <p class="font-black text-slate-950">Can I create tables with the Waitlist Pass or with Founder Join?</p>
            <p class="text-slate-700">Yes with the Waitlist Pass; no with Founder Join. During its 60-day validity period, the Waitlist Pass also includes Creator features and therefore allows you to create and manage tables according to the rules and limits of the service. Founder Join 12M includes Join features only.</p>
        </div>
        <div class="mt-4 space-y-2 rounded-lg border border-violet-200 bg-violet-50/60 p-4">
            <p class="font-black text-slate-950">Founder Creator requires an additional selection?</p>
            <p class="text-slate-700">No. There is no additional discretionary selection for those who buy it. Standard account requirements are required and normal security, moderation, fair use and quality limits apply.</p>
        </div>
        <div class="mt-4 space-y-2 rounded-lg border border-violet-200 bg-violet-50/60 p-4">
            <p class="font-black text-slate-950">Founder Pass starts on the day of purchase?</p>
            <p class="text-slate-700">No. It can be activated by the public go-live of the app in Milan and lasts 12 months from your correct individual activation; the period between go-live and activation is not lost.</p>
        </div>
        <div class="mt-4 space-y-2 rounded-lg border border-violet-200 bg-violet-50/60 p-4">
            <p class="font-black text-slate-950">Does Founder Pass automatically renew?</p>
            <p class="text-slate-700">No. It ends after 12 months without additional charges.</p>
        </div>
        <div class="mt-4 space-y-2 rounded-lg border border-violet-200 bg-violet-50/60 p-4">
            <p class="font-black text-slate-950">Can I add the Founder Pass to 60 free days?</p>
            <p class="text-slate-700">No, unless otherwise express WAYOUT communication. Founder Pass replaces free benefit and incompatible promotions.</p>
        </div>
        <div class="mt-4 space-y-2 rounded-lg border border-violet-200 bg-violet-50/60 p-4">
            <p class="font-black text-slate-950">Does the Pass guarantee entry to a local or physical table?</p>
            <p class="text-slate-700">No. The Pass covers WAYOUT digital features. Entrances, bookings, consummations and services of the premises are separated.</p>
        </div>
        <div class="mt-4 space-y-2 rounded-lg border border-violet-200 bg-violet-50/60 p-4">
            <p class="font-black text-slate-950">Does the Pass ensure my request is accepted?</p>
            <p class="text-slate-700">No. Requests can be accepted, rejected, withdrawn or expired and depend on capacity, rules, security and availability of users.</p>
        </div>
        <div class="mt-4 space-y-2 rounded-lg border border-violet-200 bg-violet-50/60 p-4">
            <p class="font-black text-slate-950">What if the app is not launched by 31 December 2026?</p>
            <p class="text-slate-700">You can choose, within the announced window, if you keep the Founder Pass for the next go-live or request full refund.</p>
        </div>
        <div class="mt-4 space-y-2 rounded-lg border border-violet-200 bg-violet-50/60 p-4">
            <p class="font-black text-slate-950">Where do I find the full rules?</p>
            <p class="text-slate-700">In <a class="font-black text-violet-700" href="/termini-e-condizioni">Terms of use of the site and waitlist</a>, <a class="font-black text-violet-700" href="/termini-di-vendita">Terms of sale online</a>, in <a class="font-black text-violet-700" href="/condizioni-di-pre-sale">Specific pre-sale conditions</a>, on page <a class="font-black text-violet-700" href="/recedere-dal-contratto">Withdrawal and refunds</a> and in <a class="font-black text-violet-700" href="/privacy-policy">Privacy Policy</a>.</p>
        </div>
    </section>

    <section>
        <h2 class="text-2xl font-black text-slate-950">10. Applicable documents and assistance</h2>
        <p class="mt-3">This page explains how the Passes work in plain language. It does not replace the full contractual documents. In the event of differences or doubts, reference must be made to the <a class="font-black text-violet-700" href="/termini-e-condizioni">Website and waitlist terms</a>, the <a class="font-black text-violet-700" href="/termini-di-vendita">Online sales terms</a>, the <a class="font-black text-violet-700" href="/condizioni-di-pre-sale">Specific pre-sale terms</a>, the <a class="font-black text-violet-700" href="/recedere-dal-contratto">Withdrawal and refunds</a> information and policy, the future App Terms and applicable mandatory rules.</p>
        <p class="mt-3"><em><strong>General assistance: <a class="font-black text-violet-700" href="mailto:amministrazione@wayoutapp.it">amministrazione@wayoutapp.it</a></strong></em></p>
        <p class="mt-3"><em><strong>Fees, refunds, billing and payments: </strong></em><a class="font-black text-violet-700" href="mailto:amministrazione@wayoutapp.it">amministrazione@wayoutapp.it</a></p>
        <p class="mt-3"><em><strong>PEC: </strong></em><a class="font-black text-violet-700" href="mailto:wayout@pec.wayoutapp.it">wayout@pec.wayoutapp.it</a></p>
        <div class="mt-4 space-y-2 rounded-lg border border-violet-200 bg-violet-50/60 p-4">
            <p class="font-black text-slate-950"><em><strong>Before buying. </strong></em></p>
            <p class="text-slate-700">Always consult the summary of the plan, the total price, the decor, the duration, the absence of automatic renewal and the contractual documents available in the checkout.</p>
        </div>
        <p class="mt-3">Last update: 27 August 2026</p>
    </section>
</div>
@endsection
