<div class="store-downloads">
    @foreach(['apple' => 'App Store', 'google' => 'Google Play'] as $store => $label)
        @php
            $storeUrl = config('services.app_stores.'.$store);
        @endphp
        <{{ $storeUrl ? 'a' : 'button' }} @if($storeUrl) href="{{ $storeUrl }}" @else type="button" disabled @endif class="store-button">
            @if($store === 'apple')
                <svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M17.1 12.5c0-2 1.6-3 1.7-3.1-1-1.5-2.6-1.7-3.2-1.7-1.4-.2-2.7.9-3.4.9s-1.8-.9-3-.9c-1.5 0-2.8.8-3.6 2.1-1.6 2.7-.4 6.7 1.1 8.9.7 1.1 1.5 2.2 2.7 2.1 1.1 0 1.6-.7 3-.7s1.8.7 3 .7 2-1 2.7-2.1c.8-1.2 1.1-2.4 1.1-2.5-.1 0-2.1-.8-2.1-3.7ZM14.9 6.2c.6-.8 1.1-1.9 1-3-.9 0-2.1.6-2.8 1.4-.6.7-1.2 1.8-1 2.9 1 .1 2.1-.5 2.8-1.3Z"/></svg>
            @else
                <svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="m4 3 10 9L4 21V3Z" fill="#64D981"/><path d="m4 3 13 7-3 2L4 3Z" fill="#5DC6F3"/><path d="m14 12 3 2-13 7 10-9Z" fill="#FC6A79"/><path d="m17 10 3 1.6c.3.2.3.6 0 .8L17 14l-3-2 3-2Z" fill="#FFD15C"/></svg>
            @endif
            <span><small>{{ $storeUrl ? __('experience.get_app') : __('experience.soon') }}</small><strong>{{ $label }}</strong></span>
        </{{ $storeUrl ? 'a' : 'button' }}>
    @endforeach
</div>
