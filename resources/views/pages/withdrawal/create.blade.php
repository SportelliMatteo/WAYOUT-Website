@extends('pages.legal.layout', [
    'title' => $title,
    'description' => $description,
    'version' => $version,
    'updated' => $updated,
])

@section('legal-content')
<div class="space-y-12">
    <section id="recedere" class="overflow-hidden rounded-[1.5rem] border-2 border-violet-300 bg-gradient-to-br from-violet-50 to-white shadow-lg shadow-violet-900/10">
        <div class="bg-slate-950 px-5 py-6 text-white sm:px-8">
            <p class="text-xs font-black uppercase tracking-[0.22em] text-violet-300">{{ __('messages.withdrawal.online_function') }}</p>
            <h2 class="mt-3 text-3xl font-black sm:text-4xl">{{ __('messages.withdrawal.withdraw_here') }}</h2>
            <p class="mt-3 max-w-2xl font-semibold leading-7 text-slate-300">{{ __('messages.withdrawal.form_intro') }}</p>
        </div>

        <form method="POST" action="{{ route('withdrawal.review.store') }}" class="grid gap-5 p-5 sm:grid-cols-2 sm:p-8">
            @csrf
            <div class="absolute -left-[10000px]" aria-hidden="true">
                <label for="website">{{ __('messages.withdrawal.website') }}</label>
                <input id="website" name="website" tabindex="-1" autocomplete="off">
            </div>

            @if ($errors->any() && ! session('order_not_found'))
                <div class="rounded-lg border border-rose-200 bg-rose-50 p-4 font-bold text-rose-800 sm:col-span-2" role="alert">
                    {{ $errors->first() }}
                </div>
            @endif

            <label class="grid gap-2 font-black text-slate-800">
                {{ __('messages.withdrawal.first_name') }}
                <input name="first_name" value="{{ old('first_name') }}" required autocomplete="given-name" maxlength="100" class="rounded-xl border border-slate-300 bg-white px-4 py-3 font-semibold focus:border-violet-500 focus:outline-none focus:ring-4 focus:ring-violet-100">
            </label>
            <label class="grid gap-2 font-black text-slate-800">
                {{ __('messages.withdrawal.last_name') }}
                <input name="last_name" value="{{ old('last_name') }}" required autocomplete="family-name" maxlength="100" class="rounded-xl border border-slate-300 bg-white px-4 py-3 font-semibold focus:border-violet-500 focus:outline-none focus:ring-4 focus:ring-violet-100">
            </label>
            <label class="grid gap-2 font-black text-slate-800 sm:col-span-2">
                {{ __('messages.withdrawal.order_reference') }}
                <input name="order_reference" value="{{ old('order_reference') }}" required maxlength="100" placeholder="{{ __('messages.withdrawal.order_placeholder') }}" class="rounded-xl border border-slate-300 bg-white px-4 py-3 font-semibold focus:border-violet-500 focus:outline-none focus:ring-4 focus:ring-violet-100">
            </label>
            <label class="grid gap-2 font-black text-slate-800">
                {{ __('messages.withdrawal.purchase_email') }}
                <input type="email" name="purchase_email" value="{{ old('purchase_email') }}" required autocomplete="email" class="rounded-xl border border-slate-300 bg-white px-4 py-3 font-semibold focus:border-violet-500 focus:outline-none focus:ring-4 focus:ring-violet-100">
            </label>
            <label class="grid gap-2 font-black text-slate-800">
                {{ __('messages.withdrawal.receipt_email') }}
                <input type="email" name="receipt_email" value="{{ old('receipt_email') }}" required autocomplete="email" class="rounded-xl border border-slate-300 bg-white px-4 py-3 font-semibold focus:border-violet-500 focus:outline-none focus:ring-4 focus:ring-violet-100">
            </label>
            <label class="grid gap-2 font-black text-slate-800">
                {{ __('messages.withdrawal.purchase_date') }} <span class="text-xs font-semibold text-slate-500">({{ __('messages.withdrawal.optional') }})</span>
                <input type="date" name="purchase_date" value="{{ old('purchase_date') }}" max="{{ now()->toDateString() }}" class="rounded-xl border border-slate-300 bg-white px-4 py-3 font-semibold focus:border-violet-500 focus:outline-none focus:ring-4 focus:ring-violet-100">
            </label>
            <label class="grid gap-2 font-black text-slate-800">
                {{ __('messages.withdrawal.purchased_pass') }}
                <select name="plan" required class="rounded-xl border border-slate-300 bg-white px-4 py-3 font-semibold focus:border-violet-500 focus:outline-none focus:ring-4 focus:ring-violet-100">
                    <option value="">{{ __('messages.withdrawal.select') }}</option>
                    <option value="join" @selected(old('plan') === 'join')>Founder Join 12M</option>
                    <option value="creator" @selected(old('plan') === 'creator')>Founder Creator 12M</option>
                    <option value="other" @selected(old('plan') === 'other')>{{ __('messages.withdrawal.other_plan') }}</option>
                </select>
            </label>

            <div class="rounded-lg bg-slate-100 p-4 text-sm font-semibold leading-6 text-slate-600 sm:col-span-2">
                {!! __('messages.withdrawal.privacy_notice', ['privacy' => '<a href="'.e(route('legal.privacy', ['lang' => app()->getLocale()])).'" class="font-black text-violet-700">Privacy policy</a>']) !!}
            </div>
            <button type="submit" class="rounded-xl bg-violet-700 px-6 py-4 text-lg font-black text-white shadow-lg shadow-violet-700/20 transition hover:bg-violet-800 focus:outline-none focus:ring-4 focus:ring-violet-300 sm:col-span-2">
                {{ __('messages.withdrawal.review_declaration') }}
            </button>
        </form>

        @if (session('order_not_found'))
            <dialog id="order-not-found-modal" class="fixed inset-0 m-auto w-[calc(100%-2rem)] max-w-lg rounded-[1.5rem] border-0 p-0 shadow-2xl backdrop:bg-slate-950/60">
                <div class="p-6 text-center sm:p-8">
                    <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-full bg-rose-100 text-2xl font-black text-rose-700">!</div>
                    <h3 class="mt-5 text-2xl font-black text-slate-950">{{ __('messages.withdrawal.order_not_found_title') }}</h3>
                    <p class="mt-3 text-lg font-bold leading-7 text-slate-700">{{ __('messages.withdrawal.order_not_found_text') }}</p>
                    <form method="dialog" class="mt-6">
                        <button class="w-full rounded-xl bg-slate-950 px-5 py-3 font-black text-white hover:bg-violet-800">{{ __('messages.withdrawal.close_and_check') }}</button>
                    </form>
                </div>
            </dialog>
            <script nonce="{{ \Illuminate\Support\Facades\Vite::cspNonce() }}">
                document.getElementById('order-not-found-modal')?.showModal();
            </script>
        @endif
    </section>

    <section id="informativa-recesso">
        <div class="mb-6 border-b border-slate-200 pb-5">
            <p class="text-xs font-black uppercase tracking-[0.2em] text-violet-700">{{ __('messages.withdrawal.notice_label') }}</p>
            <h2 class="mt-2 text-3xl font-black text-slate-950">{{ __('messages.withdrawal.notice_title') }}</h2>
        </div>
        <div class="space-y-8">{!! $withdrawalInfo->content_snapshot !!}</div>
    </section>

    <section id="refund-policy" class="rounded-[1.5rem] border-2 border-amber-300 bg-amber-50/60 p-5 sm:p-8">
        <div class="mb-6 border-b border-amber-300 pb-5">
            <p class="text-xs font-black uppercase tracking-[0.2em] text-amber-800">{{ __('messages.withdrawal.additional_rules') }}</p>
            <h2 class="mt-2 text-3xl font-black text-slate-950">Refund Policy</h2>
            <p class="mt-3 font-semibold leading-7 text-slate-700">{{ __('messages.withdrawal.additional_text') }}</p>
        </div>
        <div class="space-y-8">{!! $refundPolicy->content_snapshot !!}</div>
    </section>
</div>
@endsection
