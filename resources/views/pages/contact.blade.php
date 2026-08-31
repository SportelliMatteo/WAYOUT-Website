@extends('layouts.app', [
    'title' => __('messages.contact.title'),
    'description' => __('messages.contact.description')
])

@section('content')
<section class="py-10 lg:py-20">
    <div class="wayout-shell grid gap-10 lg:grid-cols-[0.85fr_1.15fr] lg:items-start">
        <div class="lg:sticky lg:top-32">
            <span class="inline-flex rounded-full border border-violet-200 bg-white/80 px-4 py-2 text-sm font-black uppercase tracking-[0.22em] text-violet-700">
                {{ __('messages.contact.eyebrow') }}
            </span>
            <h1 class="mt-6 text-4xl font-black leading-[0.98] text-slate-950 sm:text-6xl">
                {{ __('messages.contact.headline') }}
            </h1>
            <p class="mt-5 text-base font-medium leading-7 text-slate-600 sm:mt-6 sm:text-lg sm:leading-8">
                {{ __('messages.contact.intro') }}
            </p>
            <div class="mt-8 rounded-[2rem] bg-slate-950 p-5 text-white shadow-2xl sm:p-6">
                <p class="text-sm font-black uppercase tracking-[0.22em] text-violet-200">{{ __('messages.contact.why') }}</p>
                <div class="mt-5 space-y-4">
                    <div class="rounded-3xl bg-white/[0.08] p-4">
                        <p class="font-black">{{ __('messages.contact.club_title') }}</p>
                        <p class="mt-1 text-sm text-slate-300">{{ __('messages.contact.club_text') }}</p>
                    </div>
                    <div class="rounded-3xl bg-white/[0.08] p-4">
                        <p class="font-black">{{ __('messages.contact.collab_title') }}</p>
                        <p class="mt-1 text-sm text-slate-300">{{ __('messages.contact.collab_text') }}</p>
                    </div>
                    <div class="rounded-3xl bg-white/[0.08] p-4">
                        <p class="font-black">{{ __('messages.contact.questions_title') }}</p>
                        <p class="mt-1 text-sm text-slate-300">{{ __('messages.contact.questions_text') }}</p>
                    </div>
                    <div class="rounded-3xl bg-white/[0.08] p-4">
                        <p class="font-black">{{ __('messages.contact.purchase_title') }}</p>
                        <p class="mt-1 text-sm text-slate-300">{{ __('messages.contact.purchase_text') }}</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="wayout-panel rounded-[2rem] p-5 sm:rounded-[2.5rem] sm:p-8">
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

            <form class="space-y-6" method="POST" action="{{ route('contact.store') }}">
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

                <div class="rounded-3xl border border-slate-200 bg-slate-50 p-4">
                    <label for="privacy_accepted" class="flex items-start gap-3 text-sm font-semibold leading-6 text-slate-600">
                        <input id="privacy_accepted" name="privacy_accepted" type="checkbox" value="1" required @checked(old('privacy_accepted')) class="mt-1 h-4 w-4 shrink-0 rounded border-slate-300 text-violet-700 focus:ring-violet-500" />
                        <span class="[&_a]:font-black [&_a]:text-violet-700 [&_a]:underline [&_a]:underline-offset-4">
                            {!! $legalDocumentsHtml['contact_acceptance'] !!}
                        </span>
                    </label>
                    <p class="mt-3 text-xs font-medium leading-relaxed text-slate-500">
                        {{ __('messages.contact.privacy_note') }}
                    </p>
                    @error('privacy_accepted')<p class="mt-2 text-sm font-semibold text-rose-500">{{ $message }}</p>@enderror
                </div>

                <button type="submit" class="w-full rounded-full wayout-purple px-8 py-4 text-lg font-black text-white shadow-[0_18px_40px_rgba(124,35,245,0.32)] transition hover:scale-[1.01] sm:w-auto">
                    {{ __('messages.contact.submit') }}
                </button>
            </form>
        </div>
    </div>
</section>
@endsection
