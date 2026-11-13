@extends('pages.legal.layout', [
    'title' => $document->title,
    'description' => $document->description,
    'updated' => \Illuminate\Support\Carbon::parse($document->published_at, 'UTC')->setTimezone(config('app.display_timezone'))->translatedFormat('d F Y'),
    'version' => $document->version,
])

@section('legal-content')
    @if($archivedVersion ?? false)
        <p class="mb-6 rounded-xl bg-amber-50 p-4 font-bold">{{ __('experience.legal_archived') }} <a class="underline" href="{{ $currentDocumentUrl }}">{{ __('experience.legal_current') }}</a></p>
    @endif
    <div class="backend-legal-content">{!! $document->content_snapshot !!}</div>
    <p class="mt-8 border-t border-slate-200 pt-4"><a class="font-bold text-violet-700 underline" href="{{ route('legal.history', ['document' => $documentKey, 'scope' => $documentScope, 'lang' => app()->getLocale()]) }}">{{ __('experience.legal_history') }}</a></p>
@endsection
