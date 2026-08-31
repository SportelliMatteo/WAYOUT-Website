<?php

namespace App\Support;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

class RecaptchaVerifier
{
    public function verify(string $token, string $expectedAction): bool
    {
        $secret = (string) config('services.recaptcha.secret_key');

        if ($secret === '' || $token === '' || $expectedAction === '') {
            Log::error('reCAPTCHA verification is enabled but its configuration or token is missing.');

            return false;
        }

        try {
            $response = Http::asForm()
                ->acceptJson()
                ->timeout(max(1, (int) config('services.recaptcha.timeout', 5)))
                ->post((string) config('services.recaptcha.verify_url'), [
                    'secret' => $secret,
                    'response' => $token,
                ]);

            if (! $response->successful()) {
                Log::warning('reCAPTCHA verification endpoint returned an error.', [
                    'status' => $response->status(),
                ]);

                return false;
            }

            $result = $response->json();
            $action = is_string($result['action'] ?? null) ? $result['action'] : '';
            $score = is_numeric($result['score'] ?? null) ? (float) $result['score'] : 0.0;
            $minimumScore = min(1.0, max(0.0, (float) config('services.recaptcha.minimum_score', 0.5)));
            $verified = ($result['success'] ?? false) === true
                && hash_equals($expectedAction, $action)
                && $score >= $minimumScore;

            if (! $verified) {
                Log::notice('reCAPTCHA rejected a form submission.', [
                    'expected_action' => $expectedAction,
                    'received_action' => $action,
                    'score' => $score,
                    'error_codes' => $result['error-codes'] ?? [],
                ]);
            }

            return $verified;
        } catch (Throwable $exception) {
            Log::warning('reCAPTCHA verification request failed.', [
                ...PrivacySafeLogContext::exception($exception),
            ]);

            return false;
        }
    }
}
