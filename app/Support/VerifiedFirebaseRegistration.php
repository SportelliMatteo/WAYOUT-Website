<?php

namespace App\Support;

final readonly class VerifiedFirebaseRegistration
{
    public function __construct(
        public string $tempToken,
        public string $phoneNumber,
    ) {}
}
