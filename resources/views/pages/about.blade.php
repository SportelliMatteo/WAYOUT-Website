@extends('layouts.app', [
    'title' => __('messages.about.title'),
    'description' => __('messages.about.description')
])

@section('content')
<div class="brand-page about-page">
    <section class="about-hero">
        <div class="wayout-shell about-hero-grid">
            <div class="about-hero-copy">
                <p class="brand-eyebrow">{{ __('messages.about.eyebrow') }}</p>
                <h1>{{ __('messages.about.headline') }} <span>{{ __('messages.about.headline_highlight') }}</span></h1>
                <p class="about-intro">{{ __('messages.about.intro') }}</p>
                <a class="brand-button brand-button-primary" href="{{ route('contact', ['lang' => app()->getLocale()]) }}">{{ __('experience.about_contact') }} <span aria-hidden="true">↗</span></a>
            </div>
            <figure class="about-hero-photo">
                <img src="/images/about/about.jpg" alt="{{ __('experience.about_photo_alt') }}" width="1200" height="900" fetchpriority="high">
                <figcaption>
                    <span>{{ __('messages.about.mission') }}</span>
                    <strong>{{ __('messages.about.mission_text') }}</strong>
                </figcaption>
            </figure>
        </div>
    </section>

    <section class="about-values wayout-shell" aria-label="{{ __('experience.about_values') }}">
        @foreach([1, 2, 3] as $number)
            <article class="about-value">
                <span class="about-value-number" aria-hidden="true">0{{ $number }}</span>
                <h2>{{ __('messages.about.card_'.$number.'_title') }}</h2>
                <p>{{ __('messages.about.card_'.$number.'_text') }}</p>
            </article>
        @endforeach
    </section>

    <section class="about-story wayout-shell">
        <div class="about-story-heading">
            <p class="brand-eyebrow">{{ __('messages.about.project') }}</p>
            <h2>{{ __('messages.about.project_title') }}</h2>
        </div>
        <div class="about-story-copy">
            <p>{{ __('messages.about.project_p1') }}</p>
            <p>{{ __('messages.about.project_p2') }}</p>
        </div>
        <div class="about-manifesto">
            <p>{{ __('messages.about.project_p3') }}</p>
            <a class="brand-button brand-button-primary" href="{{ route('subscribe', ['lang' => app()->getLocale()]) }}">{{ __('experience.about_explore') }} <span aria-hidden="true">↗</span></a>
        </div>
    </section>
</div>
@endsection
