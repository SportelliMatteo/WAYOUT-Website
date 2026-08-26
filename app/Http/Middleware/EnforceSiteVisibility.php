<?php

namespace App\Http\Middleware;

use App\Support\SiteVisibility;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class EnforceSiteVisibility
{
    public function __construct(private readonly SiteVisibility $visibility) {}

    public function handle(Request $request, Closure $next): Response
    {
        if ($request->is('admin', 'admin/*') || $request->routeIs('stripe.webhook')) {
            return $next($request);
        }

        $mode = $this->visibility->mode();

        if ($mode === SiteVisibility::ONLINE) {
            return $next($request);
        }

        $status = $mode === SiteVisibility::MAINTENANCE || ! $request->isMethodSafe()
            ? Response::HTTP_SERVICE_UNAVAILABLE
            : Response::HTTP_OK;

        $response = response()->view('pages.site-unavailable', compact('mode'), $status);
        $response->headers->set('Cache-Control', 'no-store, private');
        $response->headers->set('X-Robots-Tag', 'noindex, nofollow, noarchive');

        if ($status === Response::HTTP_SERVICE_UNAVAILABLE) {
            $response->headers->set('Retry-After', '3600');
        }

        return $response;
    }
}
