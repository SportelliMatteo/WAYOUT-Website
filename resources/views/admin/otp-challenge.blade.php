<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('messages.admin.otp_title') }}</title>
    <meta name="robots" content="noindex,nofollow">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-slate-950 text-white antialiased">
    <main class="flex min-h-screen items-center justify-center px-5 py-10">
        <section class="w-full max-w-md rounded-[1.5rem] border border-white/10 bg-white/[0.08] p-6 shadow-2xl backdrop-blur-xl sm:p-8">
            <p class="text-sm font-black uppercase tracking-[0.22em] text-violet-200">{{ __('messages.admin.two_factor_authentication') }}</p>
            <h1 class="mt-3 text-3xl font-black">{{ __('messages.admin.otp_title') }}</h1>
            <p class="mt-3 text-sm font-bold leading-6 text-slate-300">{{ __('messages.admin.otp_challenge_help', ['email' => $admin->email]) }}</p>

            @if ($errors->any())
                <div class="mt-6 rounded-2xl border border-red-300/30 bg-red-500/15 px-4 py-3 text-sm font-bold">{{ $errors->first() }}</div>
            @endif

            <form action="{{ route('admin.otp.verify') }}" method="POST" class="mt-7 space-y-4">
                @csrf
                <label class="block">
                    <span class="text-sm font-black uppercase tracking-[0.16em] text-slate-300">{{ __('messages.admin.otp_or_recovery_code') }}</span>
                    <input name="code" autocomplete="one-time-code" autocapitalize="characters" autofocus required maxlength="32" class="mt-2 w-full rounded-2xl border border-white/10 bg-white px-4 py-4 text-center font-mono text-2xl font-black tracking-[0.28em] text-slate-950 outline-none focus:border-violet-400 focus:ring-4 focus:ring-violet-500/20">
                </label>
                <button class="w-full rounded-full bg-violet-600 px-6 py-4 font-black text-white hover:bg-violet-500">{{ __('messages.admin.verify_and_enter') }}</button>
            </form>

            <a href="{{ route('admin.login') }}" class="mt-5 block text-center text-sm font-black text-violet-200">{{ __('messages.admin.back_to_login') }}</a>
        </section>
    </main>
</body>
</html>
