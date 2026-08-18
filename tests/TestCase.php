<?php

namespace Tests;

use App\Contracts\PhoneVerificationService;
use App\Exceptions\PhoneVerificationException;
use App\Support\VerifiedPhone;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->app->instance(PhoneVerificationService::class, new class implements PhoneVerificationService
        {
            public function verify(string $idToken, string $expectedPhoneNumber): VerifiedPhone
            {
                if ($idToken !== 'valid-firebase-id-token') {
                    throw new PhoneVerificationException('Invalid test token.');
                }

                return new VerifiedPhone('firebase-test-user', $expectedPhoneNumber);
            }
        });
    }
}
