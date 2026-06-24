<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'Wayout' }}</title>
    <meta name="description" content="{{ $description ?? 'Wayout - Trova persone con cui condividere tavoli e serate.' }}">
    <link rel="shortcut icon" href="/favicon.jpg" type="image/jpeg" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-white text-slate-900 antialiased">
    <nav class="bg-white fixed w-full z-20 top-0 left-0 border-b border-gray-200">
        <div class="max-w-screen-2xl mx-auto flex flex-wrap items-center justify-between px-5 py-4 lg:px-20">
            <a href="{{ route('home') }}" class="flex items-center space-x-3">
                <img src="/logos/logo_black.png" class="h-8" alt="WAYOUT Logo" />
            </a>

            <button id="nav-toggle" type="button" class="md:hidden inline-flex items-center justify-center rounded-full border border-slate-200 p-2 text-slate-700 hover:bg-slate-100" aria-controls="main-nav" aria-expanded="false">
                <span class="sr-only">Apri menu</span>
                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                </svg>
            </button>

            <div id="main-nav" class="hidden w-full md:flex md:w-auto md:items-center">
                <div class="flex flex-col gap-4 text-base font-medium text-center md:flex-row md:gap-6 md:text-lg md:justify-center w-full md:w-auto">
                    <a href="{{ route('home') }}" class="{{ request()->routeIs('home') ? 'text-slate-900 font-semibold' : 'text-slate-500 hover:text-slate-900' }}">
                        Home
                    </a>
                    <a href="{{ route('about') }}" class="{{ request()->routeIs('about') ? 'text-slate-900 font-semibold' : 'text-slate-500 hover:text-slate-900' }}">
                        Chi siamo
                    </a>
                    <a href="{{ route('contact') }}" class="{{ request()->routeIs('contact') ? 'text-slate-900 font-semibold' : 'text-slate-500 hover:text-slate-900' }}">
                        Contatti
                    </a>
                </div>
            </div>
        </div>
    </nav>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const toggle = document.getElementById('nav-toggle');
            const nav = document.getElementById('main-nav');
            if (!toggle || !nav) return;
            toggle.addEventListener('click', function () {
                const isOpen = nav.classList.toggle('hidden') === false;
                toggle.setAttribute('aria-expanded', String(isOpen));
            });
        });
    </script>

    <main class="pt-28">
        @yield('content')
    </main>

    <footer class="bg-white w-full mt-16 border-t border-gray-200">
        <div class="w-full p-5 py-6 lg:py-8">
            <div class="flex flex-col justify-center items-center">
                <span class="text-sm text-gray-500 sm:text-center mb-2">
                    © {{ date('Y') }} WAYOUT. Tutti i diritti sono riservati.
                </span>
                <div class="flex mt-4 sm:justify-center sm:mt-0 gap-4">
                    <a href="https://www.instagram.com/wayout_app/" class="text-gray-500 hover:text-gray-900" aria-label="Instagram">
                        <svg viewBox="0 0 1024 1024" fill="currentColor" class="w-4 h-4">
                            <path d="M512 378.7c-73.4 0-133.3 59.9-133.3 133.3S438.6 645.3 512 645.3 645.3 585.4 645.3 512 585.4 378.7 512 378.7zM911.8 512c0-55.2.5-109.9-2.6-165-3.1-64-17.7-120.8-64.5-167.6-46.9-46.9-103.6-61.4-167.6-64.5-55.2-3.1-109.9-2.6-165-2.6-55.2 0-109.9-.5-165 2.6-64 3.1-120.8 17.7-167.6 64.5C132.6 226.3 118.1 283 115 347c-3.1 55.2-2.6 109.9-2.6 165s-.5 109.9 2.6 165c3.1 64 17.7 120.8 64.5 167.6 46.9 46.9 103.6 61.4 167.6 64.5 55.2 3.1 109.9 2.6 165 2.6 55.2 0 109.9.5 165-2.6 64-3.1 120.8-17.7 167.6-64.5 46.9-46.9 61.4-103.6 64.5-167.6 3.2-55.1 2.6-109.8 2.6-165zM512 717.1c-113.5 0-205.1-91.6-205.1-205.1S398.5 306.9 512 306.9 717.1 398.5 717.1 512 625.5 717.1 512 717.1zm213.5-370.7c-26.5 0-47.9-21.4-47.9-47.9s21.4-47.9 47.9-47.9 47.9 21.4 47.9 47.9a47.84 47.84 0 01-47.9 47.9z" />
                        </svg>
                    </a>
                    <a href="https://www.tiktok.com/@wayout_app" class="text-gray-500 hover:text-gray-900" aria-label="TikTok">
                        <svg viewBox="0 0 448 512" fill="currentColor" class="w-4 h-4">
                            <path d="M448 209.91a210.06 210.06 0 01-122.77-39.25v178.72A162.55 162.55 0 11185 188.31v89.89a74.62 74.62 0 1052.23 71.18V0h88a121.18 121.18 0 001.86 22.17A122.18 122.18 0 00381 102.39a121.43 121.43 0 0067 20.14z" />
                        </svg>
                    </a>
                </div>
                <a href="{{ route('home') }}" class="flex items-center mb-4 sm:mb-0 space-x-3">
                    <img src="/logos/logo_black.png" class="h-8 mt-5" alt="WAYOUT Logo" />
                </a>
            </div>
        </div>
    </footer>
</body>
</html>
