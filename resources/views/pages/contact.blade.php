@extends('layouts.app', [
    'title' => __('messages.contact.title'),
    'description' => __('messages.contact.description')
])

@section('content')
<section class="brand-page contact-page">
    <div class="wayout-shell contact-layout">
        <div class="contact-copy">
            <p class="brand-eyebrow">{{ __('messages.contact.eyebrow') }}</p>
            <h1>{{ __('messages.contact.headline') }}</h1>
            <p class="contact-intro">{{ __('messages.contact.intro') }}</p>
            <div class="contact-reasons">
                <h2>{{ __('messages.contact.why') }}</h2>
                @foreach(['club', 'collab', 'questions', 'purchase'] as $reason)
                    <article class="contact-reason">
                        <span aria-hidden="true">0{{ $loop->iteration }}</span>
                        <div><h3>{{ __('messages.contact.'.$reason.'_title') }}</h3><p>{{ __('messages.contact.'.$reason.'_text') }}</p></div>
                    </article>
                @endforeach
            </div>
        </div>

        <div class="contact-form-panel">
            <header class="contact-form-heading">
                <p class="brand-eyebrow">{{ __('experience.contact_form_eyebrow') }}</p>
                <h2>{{ __('experience.contact_form_title') }}</h2>
            </header>
            @if(session('contact_success'))
                <span class="hidden" data-analytics-page-event="contact_submitted" data-analytics-dedupe="contact_submitted"></span>
                <div class="mb-6 rounded-3xl border border-emerald-300 bg-emerald-50 p-5 font-semibold text-emerald-900">
                    {{ __('messages.contact.success') }}
                </div>
            @endif

            @if(session('contact_error'))
                <div class="mb-6 rounded-3xl border border-rose-300 bg-rose-50 p-5 font-semibold text-rose-900">
                    {{ session('contact_error') }}
                </div>
            @endif

            <form class="contact-form" method="POST" action="{{ route('contact.store') }}">
                @csrf
                @include('partials.recaptcha', ['action' => 'contact'])
                <input type="text" name="website" value="" tabindex="-1" autocomplete="off" class="hidden" aria-hidden="true" />

                <div class="grid gap-6 sm:grid-cols-2">
                    <div>
                        <label for="name" class="mb-2 block text-sm font-black text-slate-950">{{ __('messages.contact.name') }}</label>
                        <input type="text" id="name" name="name" value="{{ old('name') }}" required class="block min-h-14 w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 text-base font-semibold text-slate-950 outline-none transition placeholder:text-slate-400 focus:border-violet-400 focus:bg-white focus:ring-4 focus:ring-violet-100" placeholder="{{ __('messages.contact.name_placeholder') }}" />
                        @error('name')<p class="mt-2 text-sm font-semibold text-rose-500">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label for="email" class="mb-2 block text-sm font-black text-slate-950">Email</label>
                        <input type="email" id="email" name="email" value="{{ old('email') }}" required class="block min-h-14 w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 text-base font-semibold text-slate-950 outline-none transition placeholder:text-slate-400 focus:border-violet-400 focus:bg-white focus:ring-4 focus:ring-violet-100" placeholder="{{ __('messages.contact.email_placeholder') }}" />
                        @error('email')<p class="mt-2 text-sm font-semibold text-rose-500">{{ $message }}</p>@enderror
                    </div>
                </div>

                <div>
                    <label for="subject" class="mb-2 block text-sm font-black text-slate-950">{{ __('messages.contact.subject') }}</label>
                    <select id="subject" name="subject" required class="block min-h-14 w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 text-base font-semibold text-slate-950 outline-none transition focus:border-violet-400 focus:bg-white focus:ring-4 focus:ring-violet-100">
                        <option value="" disabled @selected(! old('subject'))>{{ __('messages.contact.subject_placeholder') }}</option>
                        @foreach(__('messages.contact.subject_options') as $value => $label)
                            <option value="{{ $value }}" @selected(old('subject') === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                    @error('subject')<p class="mt-2 text-sm font-semibold text-rose-500">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="message" class="mb-2 block text-sm font-black text-slate-950">{{ __('messages.contact.message') }}</label>
                    <textarea id="message" name="message" rows="7" required class="block w-full rounded-[1.5rem] border border-slate-200 bg-slate-50 p-4 text-base font-semibold text-slate-950 outline-none transition placeholder:text-slate-400 focus:border-violet-400 focus:bg-white focus:ring-4 focus:ring-violet-100" placeholder="{{ __('messages.contact.message_placeholder') }}">{{ old('message') }}</textarea>
                    @error('message')<p class="mt-2 text-sm font-semibold text-rose-500">{{ $message }}</p>@enderror
                </div>

                <div class="contact-privacy">
                    <label for="privacy_accepted" class="flex items-start gap-3 text-sm font-semibold leading-6 text-slate-600">
                        <input id="privacy_accepted" name="privacy_accepted" type="checkbox" value="1" required @checked(old('privacy_accepted')) class="mt-1 h-4 w-4 shrink-0 rounded border-slate-300 text-violet-700 focus:ring-violet-500" />
                        <span class="[&_a]:font-black [&_a]:text-violet-700 [&_a]:underline [&_a]:underline-offset-4">
                            @if($legalContentUnavailable)
                                {{ __('experience.legal_unavailable') }}
                            @else
                                {!! $legalDocumentsHtml['contact_acceptance'] !!}
                            @endif
                        </span>
                    </label>
                    <p class="mt-3 text-xs font-medium leading-relaxed text-slate-500">
                        {{ __('messages.contact.privacy_note') }}
                    </p>
                    @error('privacy_accepted')<p class="mt-2 text-sm font-semibold text-rose-500">{{ $message }}</p>@enderror
                </div>

                <button type="submit" @disabled($legalContentUnavailable) class="brand-button brand-button-primary contact-submit">
                    {{ __('messages.contact.submit') }} <span aria-hidden="true">↗</span>
                </button>
            </form>
        </div>
    </div>
</section>
@endsection
