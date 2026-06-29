@php
    $money = fn (?int $amount) => number_format(($amount ?? 0) / 100, 2, ',', '.') . '€';
    $number = fn (?int $value) => number_format($value ?? 0, 0, ',', '.');
    $date = fn ($value) => $value ? \Illuminate\Support\Carbon::parse($value)->format('d/m/Y H:i') : 'Mai';
    $planName = fn (?string $plan) => $plan ? ($planLabels[$plan] ?? ucfirst($plan)) : 'Nessun pass';
@endphp

<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin - Wayout</title>
    <meta name="robots" content="noindex,nofollow">
    <link rel="shortcut icon" href="/favicon.jpg" type="image/jpeg" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-slate-100 text-slate-950 antialiased">
    <main class="mx-auto w-full max-w-screen-2xl px-4 py-5 sm:px-6 lg:px-8">
        <header class="flex flex-col gap-4 border-b border-slate-200 pb-5 lg:flex-row lg:items-center lg:justify-between">
            <div>
                <p class="text-sm font-black uppercase tracking-[0.22em] text-violet-700">WAYOUT Admin</p>
                <h1 class="mt-2 text-3xl font-black sm:text-4xl">Waitlist e ordini</h1>
            </div>
            <form action="{{ route('admin.logout') }}" method="POST">
                @csrf
                <button type="submit" class="rounded-full border border-slate-200 bg-white px-5 py-3 text-sm font-black text-slate-700 shadow-sm transition hover:border-violet-300 hover:text-violet-700">
                    Logout
                </button>
            </form>
        </header>

        @if (session('admin_success'))
            <div class="mt-5 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-black text-emerald-800">
                {{ session('admin_success') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="mt-5 rounded-lg border border-rose-200 bg-rose-50 px-4 py-3 text-sm font-black text-rose-800">
                {{ $errors->first() }}
            </div>
        @endif

        <section class="mt-6 grid gap-4 sm:grid-cols-2 xl:grid-cols-5">
            <div class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm">
                <p class="text-xs font-black uppercase tracking-[0.18em] text-slate-500">Waitlist</p>
                <p class="mt-3 text-3xl font-black">{{ $number($stats['waitlist_total']) }}</p>
                <p class="mt-1 text-sm font-bold text-slate-500">su {{ $number($capacities['waitlist_capacity']) }} posti</p>
            </div>
            <div class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm">
                <p class="text-xs font-black uppercase tracking-[0.18em] text-slate-500">Buyer</p>
                <p class="mt-3 text-3xl font-black">{{ $stats['buyers_total'] }}</p>
                <p class="mt-1 text-sm font-bold text-slate-500">email con acquisto riuscito</p>
            </div>
            <div class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm">
                <p class="text-xs font-black uppercase tracking-[0.18em] text-slate-500">Ordini riusciti</p>
                <p class="mt-3 text-3xl font-black">{{ $stats['orders_succeeded'] }}</p>
                <p class="mt-1 text-sm font-bold text-slate-500">{{ $stats['orders_total'] }} ordini totali</p>
            </div>
            <div class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm">
                <p class="text-xs font-black uppercase tracking-[0.18em] text-slate-500">Join Pass</p>
                <p class="mt-3 text-3xl font-black">{{ $money($stats['join_revenue']) }}</p>
                <p class="mt-1 text-sm font-bold text-slate-500">{{ $number($stats['join_orders']) }} / {{ $number($capacities['join_capacity']) }} pass</p>
            </div>
            <div class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm">
                <p class="text-xs font-black uppercase tracking-[0.18em] text-slate-500">Creator Pass</p>
                <p class="mt-3 text-3xl font-black">{{ $money($stats['creator_revenue']) }}</p>
                <p class="mt-1 text-sm font-bold text-slate-500">{{ $number($stats['creator_orders']) }} / {{ $number($capacities['creator_capacity']) }} pass</p>
            </div>
        </section>

        <section class="mt-4 rounded-lg border border-slate-200 bg-slate-950 p-5 text-white shadow-sm">
            <div class="flex flex-col gap-2 sm:flex-row sm:items-end sm:justify-between">
                <div>
                    <p class="text-xs font-black uppercase tracking-[0.18em] text-violet-200">Fatturato totale</p>
                    <p class="mt-2 text-4xl font-black">{{ $money($stats['revenue_total']) }}</p>
                </div>
                <p class="text-sm font-bold text-slate-300">Somma degli ordini con stato riuscito.</p>
            </div>
        </section>

        <section class="mt-6 rounded-lg border border-slate-200 bg-white p-4 shadow-sm">
            <div class="flex flex-col gap-1 border-b border-slate-200 pb-4">
                <h2 class="text-xl font-black">Capienze Founder</h2>
                <p class="text-sm font-bold text-slate-500">Modifica i posti disponibili mostrati nel sito e nella dashboard.</p>
            </div>
            <form method="POST" action="{{ route('admin.settings.update') }}" class="mt-4 grid gap-3 lg:grid-cols-[1fr_1fr_1fr_auto] lg:items-end">
                @csrf
                <div>
                    <label for="waitlist_capacity" class="text-xs font-black uppercase tracking-[0.16em] text-slate-500">Waitlist</label>
                    <input id="waitlist_capacity" name="waitlist_capacity" type="number" min="0" max="1000000" value="{{ $capacities['waitlist_capacity'] }}" class="mt-2 w-full rounded-lg border border-slate-200 px-4 py-3 font-bold outline-none transition focus:border-violet-400 focus:ring-4 focus:ring-violet-500/10">
                </div>
                <div>
                    <label for="join_capacity" class="text-xs font-black uppercase tracking-[0.16em] text-slate-500">Join Pass</label>
                    <input id="join_capacity" name="join_capacity" type="number" min="0" max="1000000" value="{{ $capacities['join_capacity'] }}" class="mt-2 w-full rounded-lg border border-slate-200 px-4 py-3 font-bold outline-none transition focus:border-violet-400 focus:ring-4 focus:ring-violet-500/10">
                </div>
                <div>
                    <label for="creator_capacity" class="text-xs font-black uppercase tracking-[0.16em] text-slate-500">Creator Pass</label>
                    <input id="creator_capacity" name="creator_capacity" type="number" min="0" max="1000000" value="{{ $capacities['creator_capacity'] }}" class="mt-2 w-full rounded-lg border border-slate-200 px-4 py-3 font-bold outline-none transition focus:border-violet-400 focus:ring-4 focus:ring-violet-500/10">
                </div>
                <button type="submit" class="rounded-lg bg-slate-950 px-5 py-3 font-black text-white shadow-sm transition hover:bg-violet-700">
                    Salva
                </button>
            </form>
        </section>

        <section class="mt-6 rounded-lg border border-slate-200 bg-white p-4 shadow-sm">
            <form method="GET" action="{{ route('admin.dashboard') }}" class="grid gap-3 lg:grid-cols-[1fr_180px_180px_auto] lg:items-end">
                <div>
                    <label for="q" class="text-xs font-black uppercase tracking-[0.16em] text-slate-500">Cerca email</label>
                    <input id="q" name="q" type="search" value="{{ $filters['q'] }}" class="mt-2 w-full rounded-lg border border-slate-200 px-4 py-3 font-bold outline-none transition focus:border-violet-400 focus:ring-4 focus:ring-violet-500/10">
                </div>
                <div>
                    <label for="status" class="text-xs font-black uppercase tracking-[0.16em] text-slate-500">Stato</label>
                    <select id="status" name="status" class="mt-2 w-full rounded-lg border border-slate-200 px-4 py-3 font-bold outline-none transition focus:border-violet-400 focus:ring-4 focus:ring-violet-500/10">
                        <option value="all" @selected($filters['status'] === 'all')>Tutti</option>
                        <option value="buyers" @selected($filters['status'] === 'buyers')>Ha comprato</option>
                        <option value="no_purchase" @selected($filters['status'] === 'no_purchase')>No acquisto</option>
                    </select>
                </div>
                <div>
                    <label for="plan" class="text-xs font-black uppercase tracking-[0.16em] text-slate-500">Pass</label>
                    <select id="plan" name="plan" class="mt-2 w-full rounded-lg border border-slate-200 px-4 py-3 font-bold outline-none transition focus:border-violet-400 focus:ring-4 focus:ring-violet-500/10">
                        <option value="all" @selected($filters['plan'] === 'all')>Tutti</option>
                        <option value="join" @selected($filters['plan'] === 'join')>Join</option>
                        <option value="creator" @selected($filters['plan'] === 'creator')>Creator</option>
                    </select>
                </div>
                <button type="submit" class="rounded-lg wayout-purple px-5 py-3 font-black text-white shadow-[0_14px_30px_rgba(124,35,245,0.25)]">
                    Filtra
                </button>
            </form>
        </section>

        <section class="mt-6 overflow-hidden rounded-lg border border-slate-200 bg-white shadow-sm">
            <div class="flex flex-col gap-1 border-b border-slate-200 p-4 sm:flex-row sm:items-end sm:justify-between">
                <div>
                    <h2 class="text-xl font-black">Persone in waitlist</h2>
                    <p class="mt-1 text-sm font-bold text-slate-500">{{ $waitlistEntries->total() }} risultati</p>
                </div>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-left text-sm">
                    <thead class="bg-slate-50 text-xs font-black uppercase tracking-[0.14em] text-slate-500">
                        <tr>
                            <th class="px-4 py-3">Email</th>
                            <th class="px-4 py-3">Stato</th>
                            <th class="px-4 py-3">Pass</th>
                            <th class="px-4 py-3">Ordini</th>
                            <th class="px-4 py-3">Totale</th>
                            <th class="px-4 py-3">Iscrizione</th>
                            <th class="px-4 py-3">Ultimo acquisto</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($waitlistEntries as $entry)
                            @php
                                $plans = collect(explode(',', (string) $entry->plans))->filter()->map(fn ($plan) => $planName($plan))->join(', ');
                            @endphp
                            <tr class="hover:bg-violet-50/50">
                                <td class="px-4 py-3 font-black">{{ $entry->email }}</td>
                                <td class="px-4 py-3">
                                    @if ((int) $entry->orders_succeeded > 0)
                                        <span class="rounded-full bg-emerald-100 px-3 py-1 text-xs font-black text-emerald-800">Ha comprato</span>
                                    @else
                                        <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-black text-slate-600">In waitlist</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 font-bold text-slate-700">{{ $plans ?: 'Nessun pass' }}</td>
                                <td class="px-4 py-3 font-bold">{{ (int) $entry->orders_succeeded }} riusciti / {{ (int) $entry->orders_total }} totali</td>
                                <td class="px-4 py-3 font-black">{{ $money((int) $entry->revenue_total) }}</td>
                                <td class="px-4 py-3 font-bold text-slate-500">{{ $date($entry->created_at) }}</td>
                                <td class="px-4 py-3 font-bold text-slate-500">{{ $date($entry->latest_purchase_at) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-4 py-10 text-center font-bold text-slate-500">Nessun risultato trovato.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="border-t border-slate-200 p-4">
                {{ $waitlistEntries->links() }}
            </div>
        </section>

        <section class="mt-6 overflow-hidden rounded-lg border border-slate-200 bg-white shadow-sm">
            <div class="border-b border-slate-200 p-4">
                <h2 class="text-xl font-black">Ordini recenti</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-left text-sm">
                    <thead class="bg-slate-50 text-xs font-black uppercase tracking-[0.14em] text-slate-500">
                        <tr>
                            <th class="px-4 py-3">Email</th>
                            <th class="px-4 py-3">Pass</th>
                            <th class="px-4 py-3">Importo</th>
                            <th class="px-4 py-3">Stato</th>
                            <th class="px-4 py-3">Data</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($recentPurchases as $purchase)
                            <tr>
                                <td class="px-4 py-3 font-black">{{ $purchase->email }}</td>
                                <td class="px-4 py-3 font-bold">{{ $planName($purchase->plan) }}</td>
                                <td class="px-4 py-3 font-black">{{ $money((int) $purchase->amount) }}</td>
                                <td class="px-4 py-3 font-bold">{{ $purchase->status }}</td>
                                <td class="px-4 py-3 font-bold text-slate-500">{{ $date($purchase->created_at) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-4 py-10 text-center font-bold text-slate-500">Nessun ordine registrato.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>

        <section class="mt-6 grid gap-6 xl:grid-cols-2">
            @foreach (['join' => $joinBuyers, 'creator' => $creatorBuyers] as $planCode => $buyers)
                <div class="overflow-hidden rounded-lg border border-slate-200 bg-white shadow-sm">
                    <div class="border-b border-slate-200 p-4">
                        <h2 class="text-xl font-black">Utenti {{ $planName($planCode) }}</h2>
                        <p class="mt-1 text-sm font-bold text-slate-500">{{ $number($buyers->count()) }} utenti negli ultimi acquisti registrati</p>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-slate-200 text-left text-sm">
                            <thead class="bg-slate-50 text-xs font-black uppercase tracking-[0.14em] text-slate-500">
                                <tr>
                                    <th class="px-4 py-3">Email</th>
                                    <th class="px-4 py-3">Ordini</th>
                                    <th class="px-4 py-3">Totale</th>
                                    <th class="px-4 py-3">Ultimo acquisto</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @forelse ($buyers as $buyer)
                                    <tr>
                                        <td class="px-4 py-3 font-black">{{ $buyer->email }}</td>
                                        <td class="px-4 py-3 font-bold">{{ $number((int) $buyer->orders_total) }}</td>
                                        <td class="px-4 py-3 font-black">{{ $money((int) $buyer->revenue_total) }}</td>
                                        <td class="px-4 py-3 font-bold text-slate-500">{{ $date($buyer->latest_purchase_at) }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="px-4 py-10 text-center font-bold text-slate-500">Nessun acquisto per questo pass.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            @endforeach
        </section>
    </main>
</body>
</html>
