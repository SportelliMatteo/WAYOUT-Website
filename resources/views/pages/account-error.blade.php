@extends('layouts.app', ['title' => __('account.title')])
@section('content')
<section class="packages-page"><div class="wayout-shell"><div class="packages-empty" role="alert"><h1>{{ __('account.unavailable') }}</h1><a class="experience-cta mt-6" href="{{ route('customer.dashboard') }}">{{ __('experience.retry') }} ↗</a></div></div></section>
@endsection
