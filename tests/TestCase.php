<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\Http;
use Illuminate\Testing\TestResponse;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config()->set('services.stripe.direct_checkout_enabled_for_tests', true);

    }

    protected function beginPhoneRegistration(
        string $phoneNumber = '3331234567',
        string $phonePrefix = '+39',
        ?string $firebaseToken = null,
    ): TestResponse {
        $firebaseToken ??= $this->firebaseTokenForPhone($phonePrefix.$phoneNumber);

        return $this->post(route('waitlist.store'), [
            'phone_prefix' => $phonePrefix,
            'phone_number' => $phoneNumber,
            'firebase_id_token' => $firebaseToken,
        ]);
    }

    protected function firebaseTokenForPhone(string $phoneNumber): string
    {
        $encode = static fn (array $payload): string => rtrim(strtr(base64_encode(json_encode($payload, JSON_THROW_ON_ERROR)), '+/', '-_'), '=');

        return $encode(['alg' => 'RS256', 'typ' => 'JWT'])
            .'.'.$encode(['sub' => 'firebase-test-user', 'phone_number' => $phoneNumber])
            .'.test-signature';
    }

    protected function fakeWayoutRegistrationApi(): void
    {
        Http::fake([
            '*/api/auth/verify-firebase-token' => Http::response([
                'success' => true,
                'data' => ['temp_token' => 'temporary-profile-token'],
            ]),
            '*/api/auth/create-profile' => Http::response([
                'success' => true,
                'data' => ['id' => 'remote-user-id'],
            ], 201),
        ]);
    }
}
