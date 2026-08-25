<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

class VerifyBenefitApiSignature
{
    public function handle(Request $request, Closure $next): Response
    {
        $configuredKey = (string) config('benefits.server_auth.key');
        $secret = (string) config('benefits.server_auth.secret');
        $key = (string) $request->header('X-Wayout-Key');
        $timestamp = (string) $request->header('X-Wayout-Timestamp');
        $nonce = (string) $request->header('X-Wayout-Nonce');
        $signature = (string) $request->header('X-Wayout-Signature');
        $maxSkew = max(30, (int) config('benefits.server_auth.max_clock_skew_seconds', 300));

        if ($configuredKey === '' || $secret === '' || $key === '' || ! hash_equals($configuredKey, $key)
            || ! ctype_digit($timestamp) || abs(time() - (int) $timestamp) > $maxSkew
            || preg_match('/^[A-Za-z0-9_-]{16,128}$/', $nonce) !== 1
            || preg_match('/^[a-f0-9]{64}$/i', $signature) !== 1) {
            return $this->unauthorized();
        }

        $canonical = implode("\n", [
            $timestamp,
            $nonce,
            strtoupper($request->method()),
            '/'.$request->path(),
            hash('sha256', $request->getContent()),
        ]);
        $expected = hash_hmac('sha256', $canonical, $secret);

        if (! hash_equals($expected, strtolower($signature))) {
            return $this->unauthorized();
        }

        $nonceKey = 'benefit-api-nonce:'.hash('sha256', $key.'|'.$nonce);
        if (! Cache::add($nonceKey, true, now()->addSeconds($maxSkew * 2))) {
            return response()->json([
                'success' => false,
                'code' => 'REPLAY_DETECTED',
                'message' => 'Request already processed.',
            ], 409);
        }

        return $next($request);
    }

    private function unauthorized(): Response
    {
        return response()->json([
            'success' => false,
            'code' => 'INVALID_SERVER_SIGNATURE',
            'message' => 'Server authentication failed.',
        ], 401);
    }
}
