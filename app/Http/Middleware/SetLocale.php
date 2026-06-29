<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    public function handle(Request $request, Closure $next): Response
    {
        $availableLocales = ['it', 'en'];
        $locale = $request->query('lang');

        if (in_array($locale, $availableLocales, true)) {
            $request->session()->put('locale', $locale);
        }

        App::setLocale($request->session()->get('locale', config('app.locale')));

        return $next($request);
    }
}
