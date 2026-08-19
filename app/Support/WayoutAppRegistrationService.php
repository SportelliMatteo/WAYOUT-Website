<?php

namespace App\Support;

use App\Exceptions\WayoutRegistrationException;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Throwable;

class WayoutAppRegistrationService
{
    public function verifyFirebaseToken(string $firebaseToken): VerifiedFirebaseRegistration
    {
        $response = $this->post('/api/auth/verify-firebase-token', [
            'firebase_token' => $firebaseToken,
        ]);

        $tempToken = $response->header('X-Temp-Token')
            ?: $response->json('data.temp_token')
            ?: $response->json('temp_token')
            ?: $response->json('data.tempToken')
            ?: $response->json('tempToken');

        if (! is_string($tempToken) || trim($tempToken) === '') {
            if ($response->json('data.access_token') || $response->json('access_token')) {
                throw new WayoutRegistrationException(
                    'A WAYOUT profile already exists for this Firebase user.',
                    409,
                    'PROFILE_ALREADY_EXISTS',
                );
            }

            throw new WayoutRegistrationException(
                'The WAYOUT API did not return a temporary profile token.',
                $response->status(),
                'TEMP_TOKEN_MISSING',
            );
        }

        $phoneNumber = $this->phoneNumberFromVerifiedToken($firebaseToken);

        return new VerifiedFirebaseRegistration($tempToken, $phoneNumber);
    }

    /** @param array<string, string> $profile */
    public function createProfile(string $tempToken, array $profile): array
    {
        $response = $this->post('/api/auth/create-profile', $profile, [
            'X-Temp-Token' => $tempToken,
        ]);

        return $response->json() ?? [];
    }

    /** @param array<string, mixed> $payload @param array<string, string> $headers */
    private function post(string $path, array $payload, array $headers = []): Response
    {
        $baseUrl = rtrim((string) config('services.wayout_app.base_url'), '/');

        if ($baseUrl === '') {
            throw new WayoutRegistrationException('The WAYOUT API URL is not configured.');
        }

        try {
            $response = Http::baseUrl($baseUrl)
                ->acceptJson()
                ->asJson()
                ->withHeaders($headers)
                ->connectTimeout((int) config('services.wayout_app.connect_timeout', 5))
                ->timeout((int) config('services.wayout_app.timeout', 15))
                ->post($path, $payload);
        } catch (ConnectionException $exception) {
            throw new WayoutRegistrationException(
                'The WAYOUT API is currently unreachable.',
                previous: $exception,
            );
        } catch (Throwable $exception) {
            throw new WayoutRegistrationException(
                'The WAYOUT API request failed.',
                previous: $exception,
            );
        }

        if ($response->failed()) {
            throw new WayoutRegistrationException(
                (string) ($response->json('message') ?: 'The WAYOUT API rejected the registration request.'),
                $response->status(),
                is_string($response->json('code')) ? $response->json('code') : null,
            );
        }

        return $response;
    }

    private function phoneNumberFromVerifiedToken(string $firebaseToken): string
    {
        $segments = explode('.', $firebaseToken);

        if (count($segments) !== 3) {
            throw new WayoutRegistrationException('The verified Firebase token is malformed.', 401, 'FIREBASE_TOKEN_MALFORMED');
        }

        $encodedPayload = strtr($segments[1], '-_', '+/');
        $encodedPayload .= str_repeat('=', (4 - strlen($encodedPayload) % 4) % 4);
        $decodedPayload = base64_decode($encodedPayload, true);
        $payload = is_string($decodedPayload) ? json_decode($decodedPayload, true) : null;
        $phoneNumber = is_array($payload) ? ($payload['phone_number'] ?? null) : null;

        if (! is_string($phoneNumber) || preg_match('/^\+[1-9]\d{6,14}$/', $phoneNumber) !== 1) {
            throw new WayoutRegistrationException('The verified Firebase token has no valid phone number.', 401, 'FIREBASE_PHONE_MISSING');
        }

        return $phoneNumber;
    }
}
