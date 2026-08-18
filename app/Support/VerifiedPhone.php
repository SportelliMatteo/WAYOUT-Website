<?php

namespace App\Support;

final readonly class VerifiedPhone
{
    public function __construct(
        public string $uid,
        public string $phoneNumber,
    ) {}
}
