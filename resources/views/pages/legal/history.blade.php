@extends('pages.legal.layout', [
    'title' => $document->title.' — '.__('experience.legal_history'),
    'description' => $document->description,
    'version' => $document->version,
    'updated' => \Illuminate\Support\Carbon::parse($document->published_at)->translatedFormat('d F Y'),
])
@section('legal-content')
<p class="mb-6"><a class="font-bold text-violet-700 underline" href="{{ $currentDocumentUrl }}">{{ __('experience.legal_current') }}</a></p>
<ul class="divide-y divide-slate-200">
@forelse($versions as $version)
    <li class="py-4"><a class="font-bold text-violet-700 underline" href="{{ route('legal.version', ['document' => $documentKey, 'scope' => $documentScope, 'version' => $version['id'], 'lang' => app()->getLocale()]) }}">{{ $version['label'] ?? $document->title }} · {{ __('messages.legal_page.version') }} {{ $version['version'] }}</a><time class="block text-sm text-slate-500">{{ \Illuminate\Support\Carbon::parse($version['published_at'])->setTimezone(config('app.display_timezone'))->translatedFormat('d F Y') }}</time></li>
@empty
    <li>{{ __('experience.legal_history_empty') }}</li>
@endforelse
</ul>
@endsection
