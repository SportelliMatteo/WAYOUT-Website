{{ __('messages.email.contact_heading') }}

{{ __('messages.email.contact_from') }}: {{ $contact['name'] }} <{{ $contact['email'] }}>
{{ __('messages.email.contact_topic') }}: {{ $contact['subject'] }}

{{ $contact['message'] }}

{{ __('messages.email.contact_reply_hint') }}
{{ __('messages.email.contact_reason') }}
