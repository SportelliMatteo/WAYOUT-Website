@extends('pages.legal.layout', [
    'title' => $document->title,
    'description' => $document->description,
    'updated' => \Illuminate\Support\Carbon::parse($document->published_at, 'UTC')->setTimezone(config('app.display_timezone'))->translatedFormat('d F Y'),
    'version' => $document->version,
])

@section('legal-content')
    {!! $document->content_snapshot !!}
@endsection
