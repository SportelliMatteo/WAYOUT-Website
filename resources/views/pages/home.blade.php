@extends('layouts.app', ['title' => __('experience.home_title'), 'description' => __('experience.home_description')])
@section('content')
<div class="app-home">
    <section class="app-home-hero" data-app-hero>
        <img class="app-night-photo" src="/images/app/onboarding-night.png" alt="" aria-hidden="true" fetchpriority="high" width="1170" height="2532">
        <div class="app-hero-wash" aria-hidden="true"></div>
        <div class="wayout-shell app-hero-content">
            <p class="app-hero-eyebrow">{{ __('experience.home_eyebrow') }}</p>
            <h1><span>{{ __('experience.home_line1') }}</span><span>{{ __('experience.home_line2') }}</span></h1>
            <p class="app-hero-intro">{{ __('experience.home_intro') }}</p>
            @include('partials.app-downloads')
            <a href="#scopri-wayout" class="app-explore-link">{{ __('experience.home_explore') }} <span aria-hidden="true">↓</span></a>
        </div>
        <div class="app-hero-footer wayout-shell" aria-hidden="true"><img src="/images/app/wordmark-white.png" alt="" width="168" height="51"><span>01 — WAYOUT APP</span></div>
    </section>

    <section id="scopri-wayout" class="app-home-sheet" data-app-tabs>
        <div class="app-sheet-handle" aria-hidden="true"></div>
        <div class="wayout-shell">
            <header class="app-sheet-heading" data-reveal><p class="experience-kicker">{{ __('experience.home_preview_label') }}</p><h2>{{ __('experience.home_section') }}</h2><p>{{ __('experience.home_section_text') }}</p></header>
            <div class="app-feature-tabs" role="tablist" aria-label="{{ __('experience.home_preview_label') }}">
                @for($i = 0; $i < 3; $i++)<button type="button" role="tab" id="app-tab-{{ $i }}" aria-controls="app-panel-{{ $i }}" aria-selected="{{ $i === 0 ? 'true' : 'false' }}" tabindex="{{ $i === 0 ? '0' : '-1' }}" data-app-tab="{{ $i }}">{{ __('experience.home_tab'.$i) }}</button>@endfor
            </div>
            @for($i = 0; $i < 3; $i++)
            <div id="app-panel-{{ $i }}" role="tabpanel" aria-labelledby="app-tab-{{ $i }}" tabindex="0" data-app-panel="{{ $i }}" @if($i !== 0) hidden @endif>
                <div class="app-feature-panel">
                    <div class="app-feature-copy"><span class="app-feature-number">0{{ $i + 1 }}</span><h3>{{ __('experience.home_title'.$i) }}</h3><p>{{ __('experience.home_text'.$i) }}</p></div>
                    <div class="app-feature-preview preview-{{ $i }}" aria-hidden="true">
                        @if($i === 0)
                            <img class="preview-map" src="/images/app/onboarding-map.png" alt="" loading="lazy" width="1170" height="2532">
                            <div class="preview-drawer"><span class="preview-handle"></span><img src="/logos/logo_black.png" alt="" class="preview-logo" width="140" height="43"><div class="preview-search"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><circle cx="10" cy="10" r="6"/><path d="m15 15 5 5"/></svg>{{ __('experience.home_search') }}</div><div class="preview-event"><span class="preview-event-image"></span><span><small>WAYOUT</small><strong>{{ __('experience.home_events') }}</strong></span><span class="preview-event-arrow">↗</span></div></div>
                        @elseif($i === 1)
                            <p class="preview-organise-title">{{ __('experience.home_question') }}</p><div class="preview-choice selected"><img src="/images/app/table.png" alt="" width="36" height="36"><span>{{ __('experience.home_table') }}</span><b>✓</b></div><div class="preview-choice"><img src="/images/app/party.png" alt="" width="36" height="36"><span>{{ __('experience.home_party') }}</span></div>
                        @else
                            <img class="preview-chat-logo" src="/images/app/wordmark-white.png" alt="" width="130" height="39"><div class="preview-bubble bubble-first">{{ __('experience.home_chat1') }}</div><div class="preview-bubble bubble-answer">{{ __('experience.home_chat2') }}</div><div class="preview-bubble bubble-last">{{ __('experience.home_chat3') }}</div>
                        @endif
                    </div>
                </div>
            </div>
            @endfor
        </div>
    </section>

    <section class="app-home-offer wayout-shell" data-reveal><div><p class="experience-kicker">{{ __('experience.home_offer_label') }}</p><h2>{{ __('experience.home_offer_title') }}</h2><p>{{ __('experience.home_offer_text') }}</p></div><a href="{{ route('subscribe') }}" class="app-purple-button">{{ __('experience.plans_cta') }} <span aria-hidden="true">↗</span></a></section>
</div>
@endsection
