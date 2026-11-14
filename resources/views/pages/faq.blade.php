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
            <p class="mt-6 text-lg leading-8 text-slate-600" lang="it">{{ $faq['intro'] }}</p>
            <p class="mt-4 text-sm text-slate-500">{{ __('experience.faq_source_date') }}</p>
            @if(app()->getLocale() !== 'it')
                <p class="mt-2 text-sm font-semibold text-violet-700">{{ __('experience.faq_language') }}</p>
            @endif
        </header>

        <div class="mt-10 grid gap-8 lg:grid-cols-[0.3fr_0.7fr] lg:items-start">
            <nav class="rounded-3xl border border-violet-100 bg-violet-50 p-6 lg:sticky lg:top-28" aria-label="{{ __('experience.faq_categories') }}">
                <p class="mb-4 text-xs font-black uppercase tracking-widest text-violet-700">{{ __('experience.faq_categories') }}</p>
                <ol class="space-y-3" lang="it">
                    @foreach($faq['sections'] as $section)
                        <li><a class="flex gap-3 rounded-lg py-2 font-bold text-slate-800 hover:text-violet-700 focus-visible:outline-2 focus-visible:outline-violet-600" href="#{{ $section['id'] }}"><span class="text-violet-500" aria-hidden="true">0{{ $loop->iteration }}</span>{{ $section['title'] }}</a></li>
                    @endforeach
                </ol>
            </nav>

            <div class="min-w-0 space-y-12" lang="it">
                @foreach($faq['sections'] as $section)
                    <section id="{{ $section['id'] }}" class="scroll-mt-28" aria-labelledby="{{ $section['id'] }}-heading">
                        <h2 id="{{ $section['id'] }}-heading" class="mb-5 text-2xl font-black text-slate-950 sm:text-3xl">{{ $section['title'] }}</h2>
                        @if($section['id'] === 'pass-e-abbonamenti')
                            <div class="mb-5 rounded-2xl border border-violet-200 bg-violet-50 p-5 text-sm leading-6 text-slate-700" lang="{{ app()->getLocale() }}">
                                <p>{{ __('experience.faq_prices_note') }}</p>
                                <a class="mt-2 inline-block font-bold text-violet-700 underline underline-offset-4" href="{{ route('subscribe', ['lang' => app()->getLocale()]) }}">{{ __('experience.faq_plans') }} ↗</a>
                            </div>
                        @endif
                        <div class="divide-y divide-slate-200 rounded-3xl border border-slate-200 bg-white px-5 sm:px-7">
                            @foreach($section['items'] as $item)
                                <details id="faq-{{ $item['number'] }}" class="group scroll-mt-28 py-5">
                                    <summary class="cursor-pointer rounded-lg text-base font-bold leading-7 text-slate-950 marker:text-violet-600 focus-visible:outline-2 focus-visible:outline-violet-600 sm:text-lg">{{ $item['question'] }}</summary>
                                    <div class="mt-4 space-y-4 text-base leading-7 text-slate-600">
                                        @foreach(explode("\n\n", $item['answer']) as $paragraph)
                                            <p>{{ $paragraph }}</p>
                                        @endforeach
                                        @if(isset($item['table']))
                                            <div class="overflow-x-auto rounded-xl border border-slate-200" role="region" aria-label="Listino Pass del 12 novembre 2026" tabindex="0">
                                                <table class="w-full min-w-[32rem] text-left text-sm">
                                                    <caption class="p-3 text-left font-semibold">Listino di riferimento · 12 novembre 2026</caption>
                                                    <thead class="bg-violet-50 text-slate-950"><tr>@foreach($item['table']['headers'] as $heading)<th scope="col" class="px-4 py-3">{{ $heading }}</th>@endforeach</tr></thead>
                                                    <tbody class="divide-y divide-slate-100">@foreach($item['table']['rows'] as $row)<tr>@foreach($row as $cell)<td class="px-4 py-3">{{ $cell }}</td>@endforeach</tr>@endforeach</tbody>
                                                </table>
                                            </div>
                                        @endif
                                    </div>
                                </details>
                            @endforeach
                        </div>
                    </section>
                @endforeach
            </div>
        </div>

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
