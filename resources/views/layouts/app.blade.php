<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'Wayout' }}</title>
    <meta name="description" content="{{ $description ?? __('messages.home.description') }}">
    @if (! empty($robots))
        <meta name="robots" content="{{ $robots }}">
    @endif
    <link rel="shortcut icon" href="/favicon.jpg" type="image/jpeg" />
    <script nonce="{{ \Illuminate\Support\Facades\Vite::cspNonce() }}">
        (() => {
            try { if (sessionStorage.getItem('wayout-intro-seen') === '3') return; } catch {}
            document.documentElement.classList.add('wayout-loading');
            const release = () => {
                document.documentElement.classList.remove('wayout-loading');
                document.querySelector('[data-site-loader]')?.remove();
            };
            window.wayoutLoaderFallback = setTimeout(release, 6500);
            window.addEventListener('pageshow', (event) => { if (event.persisted) release(); });
        })();
    </script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="flex min-h-screen flex-col text-slate-950 antialiased {{ request()->routeIs('home') ? 'app-inspired-page' : '' }}">
    @include('partials.site-loader')
    <nav class="site-nav fixed left-0 top-0 z-30 w-full px-3 pt-3">
        <div class="site-nav-shell mx-auto flex max-w-screen-2xl items-center justify-between rounded-full border border-white/65 bg-white/[0.72] px-3 py-2 shadow-[0_18px_70px_rgba(15,23,42,0.09)] backdrop-blur-2xl lg:px-4">
            <a href="{{ route('home') }}" class="inline-flex items-center rounded-full bg-white px-3 py-2 shadow-sm ring-1 ring-slate-950/5">
                <img src="/logos/logo_black.png" class="h-7" alt="WAYOUT Logo" />
            </a>

            <button id="nav-toggle" type="button" class="inline-flex h-11 w-11 items-center justify-center rounded-full border border-slate-200 bg-white text-slate-900 shadow-sm transition hover:border-violet-300 hover:text-violet-700 md:hidden" aria-controls="mobile-nav" aria-expanded="false">
                <span class="sr-only">{{ __('messages.nav.open_menu') }}</span>
                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                </svg>
            </button>

            <div class="site-desktop-nav hidden md:flex md:w-auto md:items-center">
                <div class="site-menu flex w-auto flex-row gap-1 rounded-full border border-slate-950/5 bg-white/55 p-1 text-center text-sm font-black shadow-inner shadow-white/60">
                    <a href="{{ route('home') }}" class="rounded-full px-5 py-2 transition {{ request()->routeIs('home') ? 'bg-slate-950 text-white shadow-lg shadow-slate-950/15' : 'text-slate-500 hover:bg-white hover:text-slate-950' }}">
                        {{ __('messages.nav.home') }}
                    </a>
@if($plansEnabled)
<a href="{{ route('subscribe') }}" class="rounded-full px-5 py-2 transition {{ request()->routeIs('subscribe') ? 'bg-slate-950 text-white shadow-lg shadow-slate-950/15' : 'text-slate-500 hover:bg-white hover:text-slate-950' }}">
                        {{ __('messages.nav.plans') }}
                    </a>
                @endif
<a href="{{ route('about') }}" class="rounded-full px-5 py-2 transition {{ request()->routeIs('about') ? 'bg-slate-950 text-white shadow-lg shadow-slate-950/15' : 'text-slate-500 hover:bg-white hover:text-slate-950' }}">
                        {{ __('messages.nav.about') }}
                    </a>
                    <a href="{{ route('faq', ['lang' => app()->getLocale()]) }}" class="rounded-full px-5 py-2 transition {{ request()->routeIs('faq') ? 'bg-slate-950 text-white shadow-lg shadow-slate-950/15' : 'text-slate-500 hover:bg-white hover:text-slate-950' }}">
                        {{ __('experience.faq_nav') }}
                    </a>
<a href="{{ route('contact') }}" class="rounded-full px-5 py-2 transition {{ request()->routeIs('contact') ? 'bg-slate-950 text-white shadow-lg shadow-slate-950/15' : 'text-slate-500 hover:bg-white hover:text-slate-950' }}">
                        {{ __('messages.nav.contact') }}
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <div id="mobile-nav" class="fixed inset-0 z-40 hidden bg-slate-950/48 px-4 py-5 text-white backdrop-blur-md md:hidden" aria-hidden="true">
        <div class="flex h-full flex-col rounded-[2rem] border border-white/15 bg-slate-950/[0.58] p-5 shadow-[0_30px_100px_rgba(15,23,42,0.35)] backdrop-blur-2xl">
            <div class="flex items-center justify-between gap-4">
                <a href="{{ route('home') }}" class="inline-flex rounded-2xl bg-white/[0.92] px-4 py-3 shadow-lg shadow-slate-950/10">
                    <img src="/logos/logo_black.png" class="h-8" alt="WAYOUT Logo" />
                </a>
                <button id="nav-close" type="button" class="inline-flex h-11 w-11 items-center justify-center rounded-full border border-white/15 bg-white/10 text-white shadow-lg shadow-slate-950/10 transition hover:bg-white/20" aria-label="{{ __('messages.nav.close_menu') }}">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <div class="flex flex-1 flex-col justify-center gap-3 text-center">
                <a href="{{ route('home') }}" class="mobile-nav-link rounded-[1.5rem] border border-white/10 px-5 py-4 text-3xl font-black shadow-lg shadow-slate-950/10 backdrop-blur transition {{ request()->routeIs('home') ? 'bg-white/[0.92] text-slate-950' : 'bg-white/[0.08] text-white hover:bg-white/16' }}">
                    {{ __('messages.nav.home') }}
                </a>
@if($plansEnabled)
<a href="{{ route('subscribe') }}" class="mobile-nav-link rounded-[1.5rem] border border-white/10 px-5 py-4 text-3xl font-black shadow-lg shadow-slate-950/10 backdrop-blur transition {{ request()->routeIs('subscribe') ? 'bg-white/[0.92] text-slate-950' : 'bg-white/[0.08] text-white hover:bg-white/16' }}">
                    {{ __('messages.nav.plans') }}
                </a>
                @endif
<a href="{{ route('about') }}" class="mobile-nav-link rounded-[1.5rem] border border-white/10 px-5 py-4 text-3xl font-black shadow-lg shadow-slate-950/10 backdrop-blur transition {{ request()->routeIs('about') ? 'bg-white/[0.92] text-slate-950' : 'bg-white/[0.08] text-white hover:bg-white/16' }}">
                    {{ __('messages.nav.about') }}
                </a>
                <a href="{{ route('faq', ['lang' => app()->getLocale()]) }}" class="mobile-nav-link rounded-[1.5rem] border border-white/10 px-5 py-4 text-3xl font-black shadow-lg shadow-slate-950/10 backdrop-blur transition {{ request()->routeIs('faq') ? 'bg-white/[0.92] text-slate-950' : 'bg-white/[0.08] text-white hover:bg-white/16' }}">
                    {{ __('experience.faq_nav') }}
                </a>
<a href="{{ route('contact') }}" class="mobile-nav-link rounded-[1.5rem] border border-white/10 px-5 py-4 text-3xl font-black shadow-lg shadow-slate-950/10 backdrop-blur transition {{ request()->routeIs('contact') ? 'bg-white/[0.92] text-slate-950' : 'bg-white/[0.08] text-white hover:bg-white/16' }}">
                    {{ __('messages.nav.contact') }}
                </a>
            </div>
        </div>
    </div>

    <script nonce="{{ \Illuminate\Support\Facades\Vite::cspNonce() }}">
        document.addEventListener('DOMContentLoaded', function () {
            const toggle = document.getElementById('nav-toggle');
            const close = document.getElementById('nav-close');
            const nav = document.getElementById('mobile-nav');
            const links = document.querySelectorAll('.mobile-nav-link');
            if (!toggle || !nav) return;

            function openMenu() {
                nav.classList.remove('hidden');
                nav.setAttribute('aria-hidden', 'false');
                toggle.setAttribute('aria-expanded', 'true');
                document.body.classList.add('overflow-hidden');
            }

            function closeMenu() {
                nav.classList.add('hidden');
                nav.setAttribute('aria-hidden', 'true');
                toggle.setAttribute('aria-expanded', 'false');
                document.body.classList.remove('overflow-hidden');
            }

            toggle.addEventListener('click', openMenu);
            close?.addEventListener('click', closeMenu);
            links.forEach((link) => link.addEventListener('click', closeMenu));
            document.addEventListener('keydown', function (event) {
                if (event.key === 'Escape') closeMenu();
            });
        });
    </script>

    <main class="flex-1 pt-24">
        @yield('content')
    </main>

    @include('partials.footer')

    @if (session('meta_lead_event_id'))
        <span hidden data-analytics-page-event="waitlist_email_verified"
              data-analytics-event-id="{{ session('meta_lead_event_id') }}"
              data-analytics-dedupe="{{ session('meta_lead_event_id') }}"
              data-analytics-dedupe-scope="local"></span>
    @endif
    @include('partials.analytics-consent')
</body>
</html>
