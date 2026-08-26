@php
    $isMaintenance = $mode === \App\Support\SiteVisibility::MAINTENANCE;
@endphp
<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $isMaintenance ? __('messages.site_visibility.maintenance_title') : __('messages.site_visibility.coming_soon_title') }}</title>
    <meta name="description" content="{{ $isMaintenance ? __('messages.site_visibility.maintenance_text') : __('messages.site_visibility.coming_soon_text') }}">
    <meta name="robots" content="noindex,nofollow,noarchive">
    <link rel="shortcut icon" href="/favicon.jpg" type="image/jpeg">
    @vite(['resources/css/app.css'])
</head>
<body class="site-unavailable-page min-h-screen overflow-x-hidden antialiased">
    <main class="relative flex min-h-screen items-center justify-center overflow-hidden px-4 py-8 sm:px-5 sm:py-12">
        <div class="absolute inset-0 bg-[radial-gradient(circle_at_15%_15%,rgba(124,35,245,0.55),transparent_30rem),radial-gradient(circle_at_88%_82%,rgba(185,255,74,0.20),transparent_28rem)]"></div>
        <div class="absolute -left-28 top-1/2 h-72 w-72 -translate-y-1/2 rounded-full border border-white/10"></div>
        <div class="absolute -right-16 top-10 h-52 w-52 rounded-full border border-white/10"></div>

        <section class="relative w-full max-w-3xl rounded-[2.5rem] border border-white/15 bg-white/[0.08] px-6 py-9 text-center shadow-[0_35px_120px_rgba(0,0,0,0.45)] backdrop-blur-2xl sm:px-12 sm:py-14">
            <img src="/logos/logo_black.png" class="mx-auto h-10 rounded-xl bg-white px-4 py-3 box-content sm:h-12" alt="WAYOUT">

            <div class="mx-auto mt-10 inline-flex items-center gap-2 rounded-full border border-white/15 bg-white/10 px-4 py-2 text-xs font-black uppercase tracking-[0.22em] text-violet-100">
                <span class="h-2 w-2 rounded-full {{ $isMaintenance ? 'bg-amber-300' : 'bg-lime-300' }}"></span>
                {{ $isMaintenance ? __('messages.site_visibility.maintenance_label') : __('messages.site_visibility.coming_soon_label') }}
            </div>

            <h1 class="mt-6 text-4xl font-black tracking-tight sm:text-6xl">
                {{ $isMaintenance ? __('messages.site_visibility.maintenance_heading') : __('messages.site_visibility.coming_soon_heading') }}
            </h1>
            <p class="mx-auto mt-6 max-w-xl text-lg font-semibold leading-8 text-slate-100 sm:text-xl">
                {{ $isMaintenance ? __('messages.site_visibility.maintenance_text') : __('messages.site_visibility.coming_soon_text') }}
            </p>

            <div class="mt-10 flex justify-center gap-2 text-xs font-black">
                <a href="{{ request()->fullUrlWithQuery(['lang' => 'it']) }}" class="rounded-full border px-4 py-2 shadow-sm transition {{ app()->getLocale() === 'it' ? 'border-slate-950 bg-slate-950 text-white' : 'border-slate-300 bg-slate-100 text-slate-700 hover:bg-white hover:text-slate-950' }}">IT</a>
                <a href="{{ request()->fullUrlWithQuery(['lang' => 'en']) }}" class="rounded-full border px-4 py-2 shadow-sm transition {{ app()->getLocale() === 'en' ? 'border-slate-950 bg-slate-950 text-white' : 'border-slate-300 bg-slate-100 text-slate-700 hover:bg-white hover:text-slate-950' }}">EN</a>
            </div>
        </section>
    </main>
</body>
</html>
