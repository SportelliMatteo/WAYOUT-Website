<footer class="site-footer">
    <div class="wayout-shell">
        <div class="site-footer-grid">
            <div class="site-footer-brand">
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
            <nav class="site-footer-links" aria-label="{{ __('messages.nav.navigation') }}">
                <h2>{{ __('messages.nav.navigation') }}</h2>
                @foreach(['home' => 'home', 'subscribe' => 'plans', 'about' => 'about', 'contact' => 'contact'] as $routeName => $label)
                    <a href="{{ route($routeName, ['lang' => app()->getLocale()]) }}">{{ __('messages.nav.'.$label) }}</a>
                @endforeach
                @include('partials.app-downloads')
            </nav>
            @foreach(['SITE' => 'legal_site', 'APP' => 'legal_app'] as $scope => $label)
                <nav class="site-footer-links" aria-label="{{ __('experience.'.$label) }}" data-legal-scope="{{ $scope }}">
                    <h2>{{ __('experience.'.$label) }}</h2>
                    @forelse($footerLegalDocuments[$scope] ?? [] as $legalDocument)
                        <a href="{{ route('legal.document', ['document' => $legalDocument['code'], 'scope' => $scope, 'lang' => app()->getLocale()]) }}">{{ $legalDocument['name'] }}</a>
                    @empty
                        <p class="site-footer-empty">{{ __('experience.legal_no_documents') }}</p>
                    @endforelse
                    <a class="site-footer-all" href="{{ route('legal.index', ['scope' => $scope, 'lang' => app()->getLocale()]) }}">{{ __('experience.legal_all_documents') }} <span aria-hidden="true">↗</span></a>
                </nav>
            @endforeach
        </div>
        <div class="site-footer-company">
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
                                <dt class="font-bold text-slate-500">{{ __('messages.footer.share_capital') }}</dt>
                                <dd class="mt-1 font-semibold text-slate-200">{{ app()->getLocale() === 'it' ? '1.000,00 euro' : 'EUR 1,000.00' }}</dd>
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
        <div class="site-footer-bottom">
            <p>© {{ now()->year }} WAYOUT. {{ __('messages.footer.rights') }}</p>
            <button id="cookie-preferences-open" type="button">{{ __('messages.cookie_consent.manage') }}</button>
        </div>
    </div>
</footer>
