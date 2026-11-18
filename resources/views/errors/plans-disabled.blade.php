@extends('layouts.app', ['title' => 'Pagina non disponibile — WAYOUT', 'robots' => 'noindex,nofollow'])

@section('content')
<section class="wayout-shell py-16">
    <h1 class="text-3xl font-black">Pagina non disponibile</h1>
    <a href="{{ route('home') }}" class="mt-6 inline-block font-bold text-violet-700 underline">Torna alla home</a>
</section>
@endsection
