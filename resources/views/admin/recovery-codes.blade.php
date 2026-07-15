<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('messages.admin.recovery_codes_title') }}</title>
    <meta name="robots" content="noindex,nofollow">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-slate-950 text-white antialiased">
    <main class="flex min-h-screen items-center justify-center px-5 py-10">
        <section class="w-full max-w-xl rounded-[1.5rem] border border-white/10 bg-white/[0.08] p-6 shadow-2xl backdrop-blur-xl sm:p-8">
            <p class="text-sm font-black uppercase tracking-[0.22em] text-amber-300">{{ __('messages.admin.save_now') }}</p>
            <h1 class="mt-3 text-3xl font-black">{{ __('messages.admin.recovery_codes_title') }}</h1>
            <p class="mt-3 text-sm font-bold leading-6 text-slate-300">{{ __('messages.admin.recovery_codes_help') }}</p>

            <div class="mt-6 grid grid-cols-2 gap-2 rounded-2xl bg-white p-5 text-center font-mono font-black text-slate-950">
                @foreach($codes as $code)
                    <code class="rounded-lg bg-slate-100 px-2 py-3">{{ $code }}</code>
                @endforeach
            </div>

            <button type="button" onclick="window.print()" class="mt-4 w-full rounded-full border border-white/20 px-6 py-3 font-black hover:bg-white/10">{{ __('messages.admin.print_codes') }}</button>
            <form action="{{ route('admin.recovery-codes.acknowledge') }}" method="POST" class="mt-3">
                @csrf
                <button class="w-full rounded-full bg-violet-600 px-6 py-4 font-black text-white hover:bg-violet-500">{{ __('messages.admin.saved_codes_continue') }}</button>
            </form>
        </section>
    </main>
</body>
</html>
