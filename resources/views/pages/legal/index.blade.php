@extends('layouts.app', ['title' => __('messages.legal_page.documents')])
@section('content')
<section class="wayout-shell py-12">
    <h1 class="text-4xl font-black">{{ __('messages.legal_page.documents') }}</h1>
    <div class="mt-8 grid gap-6 lg:grid-cols-2">
    @foreach(['SITE' => 'legal_site', 'APP' => 'legal_app'] as $scope => $label)
        @if(!request()->query('scope') || request()->query('scope') === $scope)
        <section class="rounded-2xl border border-slate-200 bg-white p-6" data-legal-scope="{{ $scope }}">
            <h2 class="text-2xl font-black">{{ __('experience.'.$label) }}</h2>
            <ul class="mt-4 divide-y divide-slate-200">
            @forelse(collect($documents)->where('scope', $scope) as $document)
                <li class="py-5"><a class="text-lg font-bold text-violet-700 underline" href="{{ route('legal.document', ['document' => $document['code'], 'scope' => $scope, 'lang' => app()->getLocale()]) }}">{{ $document['name'] }}</a><p class="mt-1 text-sm text-slate-500">{{ __('messages.legal_page.version') }} {{ $document['current']['version'] }} · {{ \Illuminate\Support\Carbon::parse($document['current']['published_at'])->setTimezone(config('app.display_timezone'))->translatedFormat('d F Y') }}</p></li>
            @empty
                <li class="py-5">{{ __('experience.legal_no_documents') }}</li>
            @endforelse
            </ul>
        </section>
        @endif
    @endforeach
    </div>
</section>
@endsection
