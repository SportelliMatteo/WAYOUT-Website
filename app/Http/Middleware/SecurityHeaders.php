<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Vite;
use Symfony\Component\HttpFoundation\Response;

class SecurityHeaders
{
    public function handle(Request $request, Closure $next): Response
    {
        Vite::useCspNonce();

        $response = $next($request);

        if (! config('security.headers_enabled', true)) {
            return $response;
        }

        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('X-Frame-Options', 'DENY');
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->headers->set('Permissions-Policy', 'camera=(), microphone=(), geolocation=(), browsing-topics=()');
        $response->headers->set('Cross-Origin-Opener-Policy', 'same-origin-allow-popups');

        if ($this->containsSensitiveContent($request)) {
            $response->headers->set('Cache-Control', 'no-store, private');
            $response->headers->set('Pragma', 'no-cache');
            $response->headers->set('X-Robots-Tag', 'noindex, nofollow, noarchive');
            $response->headers->set('Referrer-Policy', 'no-referrer');
        }

        if (config('security.csp_enabled', false)) {
            $response->headers->set('Content-Security-Policy', $this->contentSecurityPolicy());
        }

        if (config('security.hsts_enabled', false) && $request->isSecure()) {
            $maxAge = max(300, (int) config('security.hsts_max_age', 31536000));
            $response->headers->set(
                'Strict-Transport-Security',
                "max-age={$maxAge}; includeSubDomains; preload",
            );
        }

        return $response;
    }

    private function containsSensitiveContent(Request $request): bool
    {
        return $request->is('admin', 'admin/*')
            || $request->is('waitlist/verifica/*')
            || $request->is('recedere-dal-contratto/conferma')
            || $request->is('recedere-dal-contratto/ricevuta/*');
    }

    private function contentSecurityPolicy(): string
    {
        $nonce = Vite::cspNonce();

        return implode('; ', [
            "default-src 'self'",
            "base-uri 'self'",
            "object-src 'none'",
            "frame-ancestors 'none'",
            "form-action 'self'",
            "script-src 'self' 'nonce-{$nonce}' https://js.stripe.com https://www.googletagmanager.com https://connect.facebook.net",
            "style-src 'self' 'unsafe-inline'",
            "img-src 'self' data: https://www.facebook.com https://*.facebook.com https://www.google-analytics.com https://*.google-analytics.com",
            "font-src 'self' data:",
            "connect-src 'self' https://api.stripe.com https://*.stripe.com https://www.google-analytics.com https://*.google-analytics.com https://www.googletagmanager.com https://www.facebook.com https://*.facebook.com",
            'frame-src https://js.stripe.com https://hooks.stripe.com https://checkout.stripe.com',
            "manifest-src 'self'",
            "worker-src 'self' blob:",
            'upgrade-insecure-requests',
        ]);
    }
}
