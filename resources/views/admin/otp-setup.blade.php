<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('messages.admin.otp_setup_title') }}</title>
    <meta name="robots" content="noindex,nofollow">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-slate-950 text-white antialiased">
    <main class="flex min-h-screen items-center justify-center px-5 py-10">
        <section class="w-full max-w-lg rounded-[1.5rem] border border-white/10 bg-white/[0.08] p-6 shadow-2xl backdrop-blur-xl sm:p-8">
            <p class="text-sm font-black uppercase tracking-[0.22em] text-violet-200">{{ __('messages.admin.two_factor_authentication') }}</p>
            <h1 class="mt-3 text-3xl font-black">{{ __('messages.admin.otp_setup_title') }}</h1>
            <p class="mt-3 text-sm font-bold leading-6 text-slate-300">{{ __('messages.admin.otp_setup_help') }}</p>

            <div class="mx-auto mt-6 w-fit overflow-hidden rounded-2xl bg-white p-4 text-slate-950">{!! $qrSvg !!}</div>
            <details class="mt-4 rounded-xl border border-white/10 p-4">
                <summary class="cursor-pointer text-sm font-black text-violet-200">{{ __('messages.admin.manual_setup') }}</summary>
                <code class="mt-3 block break-all rounded-lg bg-black/30 p-3 text-sm font-bold">{{ $secret }}</code>
            </details>

            @if ($errors->any())
                <div class="mt-5 rounded-2xl border border-red-300/30 bg-red-500/15 px-4 py-3 text-sm font-bold">{{ $errors->first() }}</div>
            @endif

            <form action="{{ route('admin.otp.setup.confirm') }}" method="POST" class="mt-6 space-y-4">
                @csrf
                <label class="block">
                    <span class="text-sm font-black uppercase tracking-[0.16em] text-slate-300">{{ __('messages.admin.first_otp_code') }}</span>
                    <input name="code" inputmode="numeric" autocomplete="one-time-code" autofocus required pattern="[0-9]{6}" maxlength="6" class="mt-2 w-full rounded-2xl border border-white/10 bg-white px-4 py-4 text-center font-mono text-2xl font-black tracking-[0.28em] text-slate-950 outline-none focus:border-violet-400 focus:ring-4 focus:ring-violet-500/20">
                </label>
                <button class="w-full rounded-full bg-violet-600 px-6 py-4 font-black text-white hover:bg-violet-500">{{ __('messages.admin.activate_otp') }}</button>
            </form>
        </section>
    </main>
</body>
</html>
