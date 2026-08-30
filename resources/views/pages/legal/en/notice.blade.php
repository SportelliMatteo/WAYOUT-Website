@extends('pages.legal.layout', [
    'title' => 'Note legali',
    'description' => 'Informazioni societarie e contatti ufficiali di WAYOUT.',
])

@section('legal-content')
<div class="space-y-8">
    <section>
        <h2 class="text-2xl font-black text-slate-950">1. Owner of the site</h2>
        <p class="mt-3">The site <strong>WAYOUT</strong> and its digital services are managed by <strong>WAYOUT S.r.l.</strong>, limited liability company with registered office in Via Guglielmo Marconi 24/B, 20082 Binasco (MI), Italy.</p>
    </section>

    <section>
        <h2 class="text-2xl font-black text-slate-950">2. Company data</h2>
        <dl class="mt-4 grid gap-3 sm:grid-cols-2">
            <div class="rounded-lg bg-white p-4">
                <dt class="font-black text-slate-500">Name:</dt>
                <dd class="mt-1">WAYOUT S.r.l.</dd>
            </div>
            <div class="rounded-lg bg-white p-4">
                <dt class="font-black text-slate-500">Registered office:</dt>
                <dd class="mt-1">Via Guglielmo Marconi 24/B, 20082 Binasco (MI), Italy</dd>
            </div>
            <div class="rounded-lg bg-white p-4">
                <dt class="font-black text-slate-500">Tax code and VAT:</dt>
                <dd class="mt-1">14805930964</dd>
            </div>
            <div class="rounded-lg bg-white p-4">
                <dt class="font-black text-slate-500">Business register:</dt>
                <dd class="mt-1">Milan Monza Brianza Lodi</dd>
            </div>
            <div class="rounded-lg bg-white p-4">
                <dt class="font-black text-slate-500">REA number:</dt>
                <dd class="mt-1">MI-2808098</dd>
            </div>
            <div class="rounded-lg bg-white p-4">
                <dt class="font-black text-slate-500">Social capital:</dt>
                <dd class="mt-1">Euro 1,000,00</dd>
            </div>
            <div class="rounded-lg bg-white p-4 sm:col-span-2">
                <dt class="font-black text-slate-500">Company asset:</dt>
                <dd class="mt-1">society is not unpersonal</dd>
            </div>
        </dl>
    </section>

    <section>
        <h2 class="text-2xl font-black text-slate-950">3. Official contacts</h2>
        <dl class="mt-4 space-y-3">
            <div>
                <dt class="font-black text-slate-950">Managing Director:</dt>
                <dd>Matteo Sportelli - <a class="font-black text-violet-700" href="mailto:matteo.sportelli@wayoutapp.it">matteo.sportelli@wayoutapp.it</a></dd>
            </div>
            <div>
                <dt class="font-black text-slate-950">Administration and contractual assistance:</dt>
                <dd><a class="font-black text-violet-700" href="mailto:amministrazione@wayoutapp.it">amministrazione@wayoutapp.it</a></dd>
            </div>
            <div>
                <dt class="font-black text-slate-950">Certified electronic mail (PEC):</dt>
                <dd><a class="font-black text-violet-700" href="mailto:wayout@pec.wayoutapp.it">wayout@pec.wayoutapp.it</a></dd>
            </div>
        </dl>
        <p class="mt-3">Communications requiring formal value or proof of sending may be transmitted to the PEC listed above.</p>
    </section>

    <section>
        <h2 class="text-2xl font-black text-slate-950">4. Use of the site and applicable documents</h2>
        <p class="mt-3">Access and use of the site are governed by <a class="font-black text-violet-700" href="/termini-e-condizioni">Terms of use of the site and waitlist</a>. The processing of personal data and the use of cookies or other tracking tools are described in <a class="font-black text-violet-700" href="/privacy-policy">Privacy Policy</a> and in <a class="font-black text-violet-700" href="/cookie-policy">Cookie Policy</a> published on the website.</p>
        <p class="mt-3">Any online purchase of <strong>Founder Pass</strong> is also regulated by contractual and pre-contractual documents made available before payment and recalled in checkout.</p>
    </section>

    <section>
        <h2 class="text-2xl font-black text-slate-950">5. Intellectual property</h2>
        <p class="mt-3">Unless otherwise indicated, trademarks, logos, names, software, interfaces, texts, graphic content and other materials on the site belong to <strong>WAYOUT S.r.l.</strong> or are used on the basis of suitable rights or authorizations. Any reproduction, distribution, modification or unauthorized use shall be prohibited within the limits laid down by law and <strong>Terms of use</strong>.</p>
    </section>

    <section>
        <h2 class="text-2xl font-black text-slate-950">6. Updates</h2>
        <p class="mt-3">The present <strong>Legal notes</strong> can be updated to reflect corporate, organizational or regulatory changes. The current version is that published on the site with its update date.</p>
    </section>
</div>
@endsection
