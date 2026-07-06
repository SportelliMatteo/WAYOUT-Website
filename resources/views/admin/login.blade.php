<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('messages.admin.login_title') }}</title>
    <meta name="robots" content="noindex,nofollow">
    <link rel="shortcut icon" href="/favicon.jpg" type="image/jpeg" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-slate-950 text-white antialiased">
    <main class="flex min-h-screen items-center justify-center px-5 py-10">
        <section class="w-full max-w-md rounded-[1.5rem] border border-white/10 bg-white/[0.08] p-6 shadow-[0_30px_90px_rgba(0,0,0,0.28)] backdrop-blur-xl sm:p-8">
            <img src="/logos/logo_black.png" class="h-9 rounded-xl bg-white px-3 py-2" alt="WAYOUT Logo" />

            <div class="mt-8">
                <p class="text-sm font-black uppercase tracking-[0.22em] text-violet-200">{{ __('messages.admin.reserved_area') }}</p>
                <h1 class="mt-3 text-3xl font-black">{{ __('messages.admin.dashboard_admin') }}</h1>
            </div>

            @if ($errors->any())
                <div class="mt-6 rounded-2xl border border-red-300/30 bg-red-500/12 px-4 py-3 text-sm font-bold text-black-100">
                    {{ $errors->first() }}
                </div>
            @endif

            <form action="{{ route('admin.authenticate') }}" method="POST" class="mt-7 space-y-4">
                @csrf
                <div>
                    <label for="email" class="text-sm font-black uppercase tracking-[0.16em] text-slate-300">{{ __('messages.admin.email') }}</label>
                    <input id="email" name="email" type="email" value="{{ old('email') }}" autocomplete="username" required class="mt-2 w-full rounded-2xl border border-white/10 bg-white px-4 py-3 font-bold text-slate-950 outline-none transition focus:border-violet-400 focus:ring-4 focus:ring-violet-500/20">
                </div>

                <div>
                    <label for="password" class="text-sm font-black uppercase tracking-[0.16em] text-slate-300">{{ __('messages.admin.password') }}</label>
                    <input id="password" name="password" type="password" autocomplete="current-password" required class="mt-2 w-full rounded-2xl border border-white/10 bg-white px-4 py-3 font-bold text-slate-950 outline-none transition focus:border-violet-400 focus:ring-4 focus:ring-violet-500/20">
                </div>

                <button type="submit" class="w-full rounded-full wayout-purple px-6 py-4 text-base font-black text-white shadow-[0_18px_40px_rgba(124,35,245,0.32)] transition hover:scale-[1.01]">
                    {{ __('messages.admin.enter') }}
                </button>
            </form>
            <div class="mt-5 flex justify-center gap-2 text-xs font-black">
                <a href="{{ request()->fullUrlWithQuery(['lang' => 'it']) }}" class="rounded-full px-3 py-2 {{ app()->getLocale() === 'it' ? 'bg-white text-slate-950' : 'bg-white/10 text-black' }}">IT</a>
                <a href="{{ request()->fullUrlWithQuery(['lang' => 'en']) }}" class="rounded-full px-3 py-2 {{ app()->getLocale() === 'en' ? 'bg-white text-slate-950' : 'bg-white/10 text-black' }}">EN</a>
            </div>
        </section>
    </main>
</body>
</html>
