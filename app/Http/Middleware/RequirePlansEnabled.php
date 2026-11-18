<?php

namespace App\Http\Middleware;

use App\Support\CheckoutFeatures;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class RequirePlansEnabled
{
    public function __construct(private readonly CheckoutFeatures $features) {}

    public function handle(Request $request, Closure $next): Response
    {
        if (! $this->features->plansEnabled()) {
            $response = $request->expectsJson()
                ? response()->json(['error' => 'Il servizio non è disponibile.', 'code' => 'PLANS_DISABLED'], 404)
                : response()->view('errors.plans-disabled', [], 404);
        } else {
            $response = $next($request);
        }
        $response->headers->set('Cache-Control', 'no-store, private');

        return $response;
    }
}
