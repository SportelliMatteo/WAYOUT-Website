<?php

namespace App\Contracts;

use App\Support\VerifiedPhone;

interface PhoneVerificationService
{
    /**
     * Verify a Firebase ID token and ensure it belongs to the expected E.164 number.
     */
    public function verify(string $idToken, string $expectedPhoneNumber): VerifiedPhone;
}
