<?php

namespace App\Http\Middleware;

use App\Support\RecaptchaVerifier;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;

class VerifyRecaptcha
{
    public function __construct(private readonly RecaptchaVerifier $verifier) {}

    public function handle(Request $request, Closure $next, string $action): Response
    {
        if (! config('services.recaptcha.enabled')) {
            return $next($request);
        }

        $token = $request->input('g-recaptcha-response');

        if (! is_string($token)
            || mb_strlen($token) > 4096
            || ! $this->verifier->verify($token, $action)) {
            throw ValidationException::withMessages([
                'recaptcha' => __('messages.messages.recaptcha_error'),
            ]);
        }

        $request->request->remove('g-recaptcha-response');

        return $next($request);
    }
}
