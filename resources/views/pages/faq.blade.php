@extends('layouts.app', [
    'title' => __('experience.faq_title').' — WAYOUT',
    'description' => __('experience.faq_description'),
])

@section('content')
<section class="py-12 lg:py-20">
    <div class="wayout-shell">
        <header class="max-w-3xl">
            <p class="brand-eyebrow">{{ __('experience.faq_title') }}</p>
            <h1 class="mt-4 text-4xl font-black leading-tight text-slate-950 sm:text-6xl">{{ __('experience.faq_intro') }}</h1>
        </header>

        @if($faqUnavailable)
            <div class="mt-10 rounded-2xl border border-rose-200 bg-rose-50 p-6 text-rose-900" role="alert">
                <p class="font-bold">Le FAQ non sono al momento disponibili. Riprova più tardi.</p>
                <a href="{{ route('faq') }}" class="mt-3 inline-block font-bold underline">Riprova</a>
            </div>
        @elseif($sections === [])
            <p class="mt-10 rounded-2xl bg-violet-50 p-6 font-semibold text-slate-700">Nessuna FAQ pubblicata al momento.</p>
        @else
            <div class="mt-10 grid gap-8 lg:grid-cols-[0.3fr_0.7fr] lg:items-start">
                <nav class="self-start rounded-3xl border border-violet-100 bg-violet-50 p-6 lg:sticky lg:top-28" aria-label="{{ __('experience.faq_categories') }}">
                    <p class="mb-4 text-xs font-black uppercase tracking-widest text-violet-700">{{ __('experience.faq_categories') }}</p>
                    <ol class="space-y-3">
                        @foreach($sections as $section)
                            @if($section['title'] !== null)
                                <li><a class="block rounded-lg py-2 font-bold text-slate-800 hover:text-violet-700 focus-visible:outline-2 focus-visible:outline-violet-600" href="#{{ $section['id'] }}">{{ $section['title'] }}</a></li>
                            @endif
                        @endforeach
                    </ol>
                    @if(count(array_filter($sections, fn ($section) => $section['title'] === null)) > 0)
                        <a class="mt-3 block rounded-lg py-2 font-bold text-slate-800 hover:text-violet-700" href="#faq-ungrouped">Altre domande</a>
                    @endif
                </nav>
                <div class="min-w-0 space-y-12">
                    @foreach($sections as $section)
                        <section id="{{ $section['id'] }}" class="scroll-mt-28" @if($section['title'] !== null) aria-labelledby="{{ $section['id'] }}-heading" @else aria-label="Altre domande" @endif>
                            @if($section['title'] !== null)
                                <h2 id="{{ $section['id'] }}-heading" class="mb-5 text-2xl font-black text-slate-950 sm:text-3xl">{{ $section['title'] }}</h2>
                            @endif
                            <div class="divide-y divide-slate-200 rounded-3xl border border-slate-200 bg-white px-5 sm:px-7">
                                @foreach($section['items'] as $item)
                                    <details id="faq-{{ $item['id'] }}" class="group scroll-mt-28 py-5">
                                        <summary class="cursor-pointer rounded-lg text-base font-bold leading-7 text-slate-950 marker:text-violet-600 focus-visible:outline-2 focus-visible:outline-violet-600 sm:text-lg">{{ $item['question'] }}</summary>
                                        <div class="mt-4 whitespace-pre-line text-base leading-7 text-slate-600">{{ $item['answer'] }}</div>
                                    </details>
                                @endforeach
                            </div>
                        </section>
                    @endforeach
                </div>
            </div>
        @endif

        <aside class="mt-14 rounded-3xl bg-violet-50 p-6 sm:p-8">
            <h2 class="text-2xl font-black text-slate-950">{{ __('experience.faq_contact') }}</h2>
            <div class="mt-5 flex flex-wrap gap-5 font-bold text-violet-700">
                <a class="underline underline-offset-4" href="{{ route('contact', ['lang' => app()->getLocale()]) }}">{{ __('messages.nav.contact') }} ↗</a>
                <a class="break-all underline underline-offset-4" href="mailto:amministrazione@wayoutapp.it">amministrazione@wayoutapp.it</a>
                <a class="underline underline-offset-4" href="{{ route('legal.index', ['scope' => 'SITE', 'lang' => app()->getLocale()]) }}">{{ __('experience.faq_legal_site') }} ↗</a>
                <a class="underline underline-offset-4" href="{{ route('legal.index', ['scope' => 'APP', 'lang' => app()->getLocale()]) }}">{{ __('experience.faq_legal') }} ↗</a>
            </div>
        </aside>
    </div>
</section>
@endsection
