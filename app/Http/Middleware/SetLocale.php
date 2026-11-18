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
        // English is temporarily disabled, including existing session preferences.
        $request->session()->put('locale', 'it');
        App::setLocale('it');

        return $next($request);
    }
}
