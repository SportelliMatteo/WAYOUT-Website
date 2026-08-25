{{ __('messages.email.greeting') }}

{{ __('messages.email.waitlist_confirmed') }}
{{ __('messages.email.waitlist_next') }}

{{ route('home') }}

@if($member['marketing_revocation_url'] ?? null)
{{ __('messages.email.revoke_marketing') }}: {{ $member['marketing_revocation_url'] }}
@endif

{{ __('messages.email.waitlist_reason', ['email' => $member['email']]) }}
WAYOUT S.R.L. - C.F./P.IVA 14805930964 - {{ config('email.support_address') }}
{{ __('messages.email.privacy_link') }}: {{ route('legal.privacy') }}
