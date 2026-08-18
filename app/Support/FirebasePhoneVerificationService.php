<?php

namespace App\Support;

use App\Contracts\PhoneVerificationService;
use App\Exceptions\PhoneVerificationException;
use Kreait\Firebase\Factory;
use Throwable;

class FirebasePhoneVerificationService implements PhoneVerificationService
{
    public function verify(string $idToken, string $expectedPhoneNumber): VerifiedPhone
    {
        $credentials = (string) config('services.firebase.credentials', '');
        $projectId = (string) config('services.firebase.project_id', '');

        if ($credentials === '') {
            throw new PhoneVerificationException('Firebase credentials are not configured.');
        }

        if (! str_starts_with(ltrim($credentials), '{') && ! str_starts_with($credentials, '/')) {
            $credentials = base_path($credentials);
        }

        try {
            $factory = (new Factory)->withServiceAccount($credentials);

            if ($projectId !== '') {
                $factory = $factory->withProjectId($projectId);
            }

            $token = $factory->createAuth()->verifyIdToken($idToken);
            $phoneNumber = (string) $token->claims()->get('phone_number');
            $uid = (string) $token->claims()->get('sub');
            $authTime = (int) $token->claims()->get('auth_time');
        } catch (Throwable $exception) {
            report($exception);

            throw new PhoneVerificationException('The Firebase phone token is invalid.', previous: $exception);
        }

        if ($phoneNumber === '' || ! hash_equals($expectedPhoneNumber, $phoneNumber)) {
            throw new PhoneVerificationException('The verified phone does not match the submitted phone.');
        }

        if ($uid === '') {
            throw new PhoneVerificationException('The Firebase user identifier is missing.');
        }

        if ($authTime > time() + 60 || $authTime < time() - 600) {
            throw new PhoneVerificationException('The Firebase phone authentication is no longer recent.');
        }

        return new VerifiedPhone($uid, $phoneNumber);
    }
}
