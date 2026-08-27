<?php

namespace App\Support;

use App\Exceptions\WayoutApiException;

class WayoutIdentityService
{
    public function __construct(
        private readonly WayoutApiClient $client,
        private readonly ProfileNicknameGenerator $nicknames,
    ) {}

    /**
     * @param  array{first_name: string, last_name: string, email: string, date_of_birth: string, gender: string}  $profile
     * @return array{id: string, created: bool, mobile_number: string}
     */
    public function resolve(string $firebaseToken, array $profile): array
    {
        $verification = $this->client->post('/api/auth/verify-firebase-token', [
            'firebase_token' => $firebaseToken,
        ]);

        $accessToken = $this->accessToken($verification->json() ?? []);
        $mobileNumber = $this->verifiedPhoneNumber($firebaseToken);

        if ($accessToken !== null) {
            return $this->identityFromAccessToken($accessToken, false, $mobileNumber);
        }

        $tempToken = $verification->header('X-Temp-Token')
            ?: data_get($verification->json(), 'data.temp_token')
            ?: data_get($verification->json(), 'temp_token');

        if (! is_string($tempToken) || trim($tempToken) === '') {
            throw new WayoutApiException(
                'Il backend WAYOUT non ha restituito un token per completare il profilo.',
                502,
                'TEMP_TOKEN_MISSING',
            );
        }

        for ($attempt = 0; $attempt < 3; $attempt++) {
            try {
                $created = $this->client->post('/api/auth/create-profile', [
                    ...$profile,
                    'mobile_number' => $mobileNumber,
                    'nickname' => $this->nicknames->generate(
                        $profile['first_name'],
                        $profile['last_name'],
                    ),
                ], [
                    'X-Temp-Token' => $tempToken,
                ]);

                $accessToken = $this->accessToken($created->json() ?? []);

                if ($accessToken === null) {
                    throw new WayoutApiException(
                        'Il backend WAYOUT non ha restituito la sessione del nuovo profilo.',
                        502,
                        'ACCESS_TOKEN_MISSING',
                    );
                }

                return $this->identityFromAccessToken($accessToken, true, $mobileNumber);
            } catch (WayoutApiException $exception) {
                if ($exception->status !== 409 || ! $this->isNicknameConflict($exception) || $attempt === 2) {
                    throw $exception;
                }
            }
        }

        throw new WayoutApiException('Impossibile generare un nickname disponibile.');
    }

    private function identityFromAccessToken(string $accessToken, bool $created, string $mobileNumber): array
    {
        $response = $this->client->bearerGet('/api/v1/users/me', $accessToken);
        $id = data_get($response, 'data.id');

        if (! is_string($id) || preg_match('/^[0-9a-f-]{36}$/i', $id) !== 1) {
            throw new WayoutApiException(
                'Il backend WAYOUT non ha restituito un identificativo utente valido.',
                502,
                'USER_ID_MISSING',
            );
        }

        return ['id' => $id, 'created' => $created, 'mobile_number' => $mobileNumber];
    }

    private function accessToken(array $payload): ?string
    {
        $token = data_get($payload, 'data.access_token') ?? data_get($payload, 'access_token');

        return is_string($token) && $token !== '' ? $token : null;
    }

    private function verifiedPhoneNumber(string $firebaseToken): string
    {
        $segments = explode('.', $firebaseToken);

        if (count($segments) !== 3) {
            throw new WayoutApiException('Il token Firebase verificato non è valido.', 401, 'FIREBASE_TOKEN_MALFORMED');
        }

        $encoded = strtr($segments[1], '-_', '+/');
        $encoded .= str_repeat('=', (4 - strlen($encoded) % 4) % 4);
        $decoded = base64_decode($encoded, true);
        $payload = is_string($decoded) ? json_decode($decoded, true) : null;
        $phone = is_array($payload) ? ($payload['phone_number'] ?? null) : null;

        if (! is_string($phone) || preg_match('/^\+[1-9]\d{6,14}$/', $phone) !== 1) {
            throw new WayoutApiException('Il token Firebase non contiene un numero valido.', 401, 'FIREBASE_PHONE_MISSING');
        }

        return $phone;
    }

    private function isNicknameConflict(WayoutApiException $exception): bool
    {
        return str_contains(strtoupper((string) $exception->apiCode), 'NICKNAME')
            || str_contains(strtolower($exception->getMessage()), 'nickname');
    }
}
