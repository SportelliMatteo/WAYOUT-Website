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
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="flex min-h-screen flex-col text-slate-950 antialiased">
    <nav class="fixed left-0 top-0 z-30 w-full px-3 pt-3">
        <div class="mx-auto flex max-w-screen-2xl items-center justify-between rounded-full border border-white/65 bg-white/[0.72] px-3 py-2 shadow-[0_18px_70px_rgba(15,23,42,0.09)] backdrop-blur-2xl lg:px-4">
            <a href="{{ route('home') }}" class="inline-flex items-center rounded-full bg-white px-3 py-2 shadow-sm ring-1 ring-slate-950/5">
                <img src="/logos/logo_black.png" class="h-7" alt="WAYOUT Logo" />
            </a>

            <button id="nav-toggle" type="button" class="inline-flex h-11 w-11 items-center justify-center rounded-full border border-slate-200 bg-white text-slate-900 shadow-sm transition hover:border-violet-300 hover:text-violet-700 md:hidden" aria-controls="mobile-nav" aria-expanded="false">
                <span class="sr-only">{{ __('messages.nav.open_menu') }}</span>
                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                </svg>
            </button>

            <div class="hidden md:flex md:w-auto md:items-center">
                <div class="flex w-auto flex-row gap-1 rounded-full border border-slate-950/5 bg-white/55 p-1 text-center text-sm font-black shadow-inner shadow-white/60">
                    <a href="{{ route('home') }}" class="rounded-full px-5 py-2 transition {{ request()->routeIs('home') ? 'bg-slate-950 text-white shadow-lg shadow-slate-950/15' : 'text-slate-500 hover:bg-white hover:text-slate-950' }}">
                        {{ __('messages.nav.home') }}
                    </a>
                    <a href="{{ route('about') }}" class="rounded-full px-5 py-2 transition {{ request()->routeIs('about') ? 'bg-slate-950 text-white shadow-lg shadow-slate-950/15' : 'text-slate-500 hover:bg-white hover:text-slate-950' }}">
                        {{ __('messages.nav.about') }}
                    </a>
                    <a href="{{ route('contact') }}" class="rounded-full px-5 py-2 transition {{ request()->routeIs('contact') ? 'bg-slate-950 text-white shadow-lg shadow-slate-950/15' : 'text-slate-500 hover:bg-white hover:text-slate-950' }}">
                        {{ __('messages.nav.contact') }}
                    </a>
                </div>
                <div class="ml-2 flex rounded-full border border-slate-950/5 bg-white/55 p-1 text-xs font-black shadow-inner shadow-white/60">
                    <a href="{{ request()->fullUrlWithQuery(['lang' => 'it']) }}" class="rounded-full px-3 py-2 transition {{ app()->getLocale() === 'it' ? 'bg-slate-950 text-white' : 'text-slate-500 hover:bg-white hover:text-slate-950' }}">IT</a>
                    <a href="{{ request()->fullUrlWithQuery(['lang' => 'en']) }}" class="rounded-full px-3 py-2 transition {{ app()->getLocale() === 'en' ? 'bg-slate-950 text-white' : 'text-slate-500 hover:bg-white hover:text-slate-950' }}">EN</a>
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
                <a href="{{ route('about') }}" class="mobile-nav-link rounded-[1.5rem] border border-white/10 px-5 py-4 text-3xl font-black shadow-lg shadow-slate-950/10 backdrop-blur transition {{ request()->routeIs('about') ? 'bg-white/[0.92] text-slate-950' : 'bg-white/[0.08] text-white hover:bg-white/16' }}">
                    {{ __('messages.nav.about') }}
                </a>
                <a href="{{ route('contact') }}" class="mobile-nav-link rounded-[1.5rem] border border-white/10 px-5 py-4 text-3xl font-black shadow-lg shadow-slate-950/10 backdrop-blur transition {{ request()->routeIs('contact') ? 'bg-white/[0.92] text-slate-950' : 'bg-white/[0.08] text-white hover:bg-white/16' }}">
                    {{ __('messages.nav.contact') }}
                </a>
                <div class="mx-auto mt-3 flex rounded-full border border-white/10 bg-white/10 p-1 text-sm font-black">
                    <a href="{{ request()->fullUrlWithQuery(['lang' => 'it']) }}" class="rounded-full px-4 py-2 {{ app()->getLocale() === 'it' ? 'bg-white text-slate-950' : 'text-white' }}">IT</a>
                    <a href="{{ request()->fullUrlWithQuery(['lang' => 'en']) }}" class="rounded-full px-4 py-2 {{ app()->getLocale() === 'en' ? 'bg-white text-slate-950' : 'text-white' }}">EN</a>
                </div>
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

    <footer class="overflow-hidden bg-slate-950 text-white">
        <div class="relative">
            <div class="absolute inset-0 bg-[radial-gradient(circle_at_12%_0%,rgba(124,35,245,0.50),transparent_30rem),radial-gradient(circle_at_85%_10%,rgba(185,255,74,0.16),transparent_24rem)]"></div>
            <div class="relative mx-auto max-w-screen-2xl px-5 py-8 lg:px-20 lg:py-10">
                <div class="grid gap-8 lg:grid-cols-[1fr_1.15fr_0.85fr] lg:gap-10">
                    <div>
                        <a href="{{ route('home') }}" class="inline-flex rounded-2xl bg-white px-4 py-3">
                            <img src="/logos/logo_black.png" class="h-8" alt="WAYOUT Logo" />
                        </a>
                        <p class="mt-6 max-w-md text-lg font-semibold leading-8 text-slate-300">
                            {{ __('messages.footer.description') }}
                        </p>
                        <div class="mt-6 flex gap-3">
                            <a href="https://www.instagram.com/wayout_app/" class="inline-flex h-11 w-11 items-center justify-center rounded-full bg-white/10 text-slate-200 transition hover:bg-violet-600 hover:text-white" aria-label="Instagram">
                                <svg viewBox="0 0 1024 1024" fill="currentColor" class="h-5 w-5">
                                    <path d="M512 378.7c-73.4 0-133.3 59.9-133.3 133.3S438.6 645.3 512 645.3 645.3 585.4 645.3 512 585.4 378.7 512 378.7zM911.8 512c0-55.2.5-109.9-2.6-165-3.1-64-17.7-120.8-64.5-167.6-46.9-46.9-103.6-61.4-167.6-64.5-55.2-3.1-109.9-2.6-165-2.6-55.2 0-109.9-.5-165 2.6-64 3.1-120.8 17.7-167.6 64.5C132.6 226.3 118.1 283 115 347c-3.1 55.2-2.6 109.9-2.6 165s-.5 109.9 2.6 165c3.1 64 17.7 120.8 64.5 167.6 46.9 46.9 103.6 61.4 167.6 64.5 55.2 3.1 109.9 2.6 165 2.6 55.2 0 109.9.5 165-2.6 64-3.1 120.8-17.7 167.6-64.5 46.9-46.9 61.4-103.6 64.5-167.6 3.2-55.1 2.6-109.8 2.6-165zM512 717.1c-113.5 0-205.1-91.6-205.1-205.1S398.5 306.9 512 306.9 717.1 398.5 717.1 512 625.5 717.1 512 717.1zm213.5-370.7c-26.5 0-47.9-21.4-47.9-47.9s21.4-47.9 47.9-47.9 47.9 21.4 47.9 47.9a47.84 47.84 0 01-47.9 47.9z" />
                                </svg>
                            </a>
                            <a href="https://www.tiktok.com/@wayout_app" class="inline-flex h-11 w-11 items-center justify-center rounded-full bg-white/10 text-slate-200 transition hover:bg-violet-600 hover:text-white" aria-label="TikTok">
                                <svg viewBox="0 0 448 512" fill="currentColor" class="h-5 w-5">
                                    <path d="M448 209.91a210.06 210.06 0 01-122.77-39.25v178.72A162.55 162.55 0 11185 188.31v89.89a74.62 74.62 0 1052.23 71.18V0h88a121.18 121.18 0 001.86 22.17A122.18 122.18 0 00381 102.39a121.43 121.43 0 0067 20.14z" />
                                </svg>
                            </a>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-6 sm:gap-8">
                        <div>
                            <p class="text-sm font-black uppercase tracking-[0.26em] text-violet-200">{{ __('messages.nav.navigation') }}</p>
                            <div class="mt-4 flex flex-col gap-2.5 text-base font-bold text-slate-300">
                                <a href="{{ route('home') }}" class="transition hover:text-white">{{ __('messages.nav.home') }}</a>
                                <a href="{{ route('about') }}" class="transition hover:text-white">{{ __('messages.nav.about') }}</a>
                                <a href="{{ route('contact') }}" class="transition hover:text-white">{{ __('messages.nav.contact') }}</a>
                            </div>
                        </div>

                        <div>
                            <p class="text-sm font-black uppercase tracking-[0.26em] text-violet-200">{{ __('messages.legal.footer_heading') }}</p>
                            <div class="mt-4 flex flex-col gap-2.5 text-sm font-bold text-slate-300">
                                <a href="{{ route('legal.privacy') }}" class="transition hover:text-white">{{ __('messages.legal.privacy') }}</a>
                                <a href="{{ route('legal.cookies') }}" class="transition hover:text-white">{{ __('messages.legal.cookies') }}</a>
                                <a href="{{ route('legal.terms') }}" class="transition hover:text-white">{{ __('messages.legal.terms') }}</a>
                                <a href="{{ route('legal.passes') }}" class="transition hover:text-white">{{ __('messages.legal.passes') }}</a>
                                <a href="{{ route('legal.sales') }}" class="transition hover:text-white">{{ __('messages.legal.sales') }}</a>
                                <a href="{{ route('legal.presale') }}" class="transition hover:text-white">{{ __('messages.legal.presale') }}</a>
                                <a href="{{ route('legal.refunds') }}" class="transition hover:text-white">{{ __('messages.legal.refunds') }}</a>
                                <a href="{{ route('legal.notice') }}" class="transition hover:text-white">{{ __('messages.legal.notice') }}</a>
                                <a href="{{ route('legal.refunds', ['lang' => app()->getLocale()]) }}#recedere" class="rounded-lg border border-violet-400/40 bg-violet-400/10 px-3 py-2 font-black text-white transition hover:bg-violet-400/20">{{ __('messages.withdrawal.withdraw_here') }}</a>
                            </div>
                        </div>
                    </div>

                    <div class="self-start rounded-[2rem] border border-white/10 bg-white/[0.06] p-5 backdrop-blur">
                        
                        <dl class="space-y-3 text-sm">
                            <div>
                                <dt class="font-bold text-slate-500">{{ __('messages.footer.company_name') }}</dt>
                                <dd class="mt-1 font-semibold text-slate-200">WAYOUT S.R.L.</dd>
                            </div>
                            <div>
                                <dt class="font-bold text-slate-500">{{ __('messages.footer.vat') }}</dt>
                                <dd class="mt-1 font-semibold text-slate-200">14805930964</dd>
                            </div>
                            <div>
                                <dt class="font-bold text-slate-500">{{ __('messages.footer.rea') }}</dt>
                                <dd class="mt-1 font-semibold text-slate-200">MI2808098</dd>
                            </div>
                            <div>
                                <dt class="font-bold text-slate-500">{{ __('messages.footer.pec') }}</dt>
                                <dd class="mt-1 font-semibold text-slate-200">
                                    <a href="mailto:wayout@pec.wayoutapp.it" class="transition hover:text-white">wayout@pec.wayoutapp.it</a>
                                </dd>
                            </div>
                            <div>
                                <dt class="font-bold text-slate-500">{{ __('messages.footer.email') }}</dt>
                                <dd class="mt-1 font-semibold text-slate-200">
                                    <a href="mailto:amministrazione@wayoutapp.it" class="transition hover:text-white">amministrazione@wayoutapp.it</a>
                                </dd>
                            </div>
                        </dl>
                    </div>
                </div>

                <div class="mt-8 flex flex-col gap-4 border-t border-white/10 pt-5 text-sm font-semibold text-slate-500 sm:flex-row sm:items-center sm:justify-between">
                    <p>© {{ date('Y') }} WAYOUT. {{ __('messages.footer.rights') }}</p>
                    <button id="cookie-preferences-open" type="button" class="text-left font-black text-slate-300 underline underline-offset-4 transition hover:text-white">{{ __('messages.cookie_consent.manage') }}</button>
                </div>
            </div>
        </div>
    </footer>
    @include('partials.analytics-consent')
</body>
</html>
