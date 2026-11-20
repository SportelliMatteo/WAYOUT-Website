<section id="admin-panel-waitlist" data-admin-panel="waitlist" class="mt-6 overflow-hidden rounded-lg border border-slate-200 bg-white shadow-sm">
    <div class="border-b border-slate-200 p-5">
        <h2 class="text-xl font-black">Utenti in waitlist</h2>
        <p class="mt-2 text-sm text-slate-600">Idoneità al benefit gratuito di 60 giorni. L’attivazione del benefit viene gestita dal backend dell’app.</p>
        <div class="mt-5 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            @foreach(['total' => 'Iscritti totali', 'verified' => 'Email verificate', 'pending' => 'In attesa di verifica', 'eligible' => 'Abilitati ai 60 giorni'] as $key => $label)
                <div class="rounded-xl bg-violet-50 p-4"><p class="text-sm font-bold text-slate-600">{{ $label }}</p><p class="mt-2 text-3xl font-black" data-waitlist-stat="{{ $key }}">{{ $number($waitlistStats[$key]) }}</p></div>
            @endforeach
        </div>
        <form method="GET" action="{{ route('admin.dashboard') }}#admin-panel-waitlist" class="mt-5 grid gap-3 sm:grid-cols-[1fr_1fr_auto]">
            <input type="hidden" name="tab" value="waitlist">
            <label class="grid gap-2 text-sm font-bold">Cerca email, ID membro o Benefit ID
                <input type="search" name="waitlist_q" value="{{ $waitlistSearch }}" maxlength="255" class="rounded-lg border border-slate-200 px-4 py-3">
            </label>
            <label class="grid gap-2 text-sm font-bold">Stato
                <select name="waitlist_status" class="rounded-lg border border-slate-200 bg-white px-4 py-3">
                    @foreach(['all' => 'Tutti', 'verified' => 'Email verificata', 'pending' => 'In attesa di verifica', 'eligible' => 'Abilitati ai 60 giorni', 'ineligible' => 'Non idonei ai 60 giorni'] as $value => $label)
                        <option value="{{ $value }}" @selected($waitlistStatus === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </label>
            <button class="self-end rounded-lg bg-violet-700 px-5 py-3 font-black text-white">Filtra</button>
        </form>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-left text-sm">
            <thead class="bg-slate-50 text-xs uppercase text-slate-500"><tr><th class="px-4 py-3">Posizione</th><th class="px-4 py-3">Email</th><th class="px-4 py-3">Consenso marketing</th><th class="px-4 py-3">Benefit ID</th><th class="px-4 py-3">Verifica email</th><th class="px-4 py-3">Benefit 60 giorni</th><th class="px-4 py-3">Iscrizione</th></tr></thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($waitlistEntries as $entry)
                    <tr>
                        <td class="px-4 py-3">{{ $entry->waitlist_position ?? '—' }}</td>
                        <td class="px-4 py-3 font-bold">{{ $entry->email }}</td>
                        <td class="px-4 py-3 font-bold {{ $entry->marketing_consent ? 'text-emerald-700' : 'text-slate-500' }}">{{ $entry->marketing_consent ? 'Sì' : 'No' }}</td>
                        <td class="px-4 py-3 break-all text-xs">{{ $entry->benefit_id ?? '—' }}<span class="mt-1 block text-slate-500">ID membro: {{ $entry->id }}</span></td>
                        <td class="px-4 py-3">{{ $entry->email_verified_at ? $date($entry->email_verified_at) : 'In attesa di verifica' }}</td>
                        <td class="px-4 py-3 font-bold {{ in_array($entry->id, $waitlistEligibleIds, true) ? 'text-emerald-700' : 'text-slate-500' }}">{{ in_array($entry->id, $waitlistEligibleIds, true) ? 'Abilitato · 60 giorni' : 'Non idoneo' }}</td>
                        <td class="px-4 py-3">{{ $date($entry->created_at) }}</td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="px-4 py-8 text-center text-slate-500">Nessun iscritto corrisponde ai filtri.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @include('admin.partials.pagination', ['paginator' => $waitlistEntries, 'perPageName' => 'waitlist_per_page'])
</section>
