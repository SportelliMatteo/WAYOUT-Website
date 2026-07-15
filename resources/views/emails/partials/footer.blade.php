<div style="margin-top:32px;padding-top:20px;border-top:1px solid #e2e8f0;color:#64748b;font-size:12px;line-height:1.6">
    <p style="margin:0 0 8px">{{ $reason }}</p>
    <p style="margin:0 0 8px">
        WAYOUT S.R.L. · C.F./P.IVA 14805930964 ·
        <a href="mailto:{{ config('email.support_address') }}" style="color:#6d28d9">{{ config('email.support_address') }}</a>
    </p>
    <p style="margin:0">
        <a href="{{ route('legal.privacy') }}" style="color:#6d28d9">{{ __('messages.email.privacy_link') }}</a>
        · <a href="{{ route('contact') }}" style="color:#6d28d9">{{ __('messages.email.contact_link') }}</a>
    </p>
</div>
