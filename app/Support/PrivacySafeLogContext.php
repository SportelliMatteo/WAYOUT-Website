<?php

namespace App\Support;

use Throwable;

final class PrivacySafeLogContext
{
    public static function fingerprint(?string $value): ?string
    {
        if (! filled($value)) {
            return null;
        }

        return hash_hmac(
            'sha256',
            mb_strtolower(trim($value)),
            (string) config('app.key', ''),
        );
    }

    /** @return array{exception_class: class-string, error_code: int|string} */
    public static function exception(Throwable $exception): array
    {
        return [
            'exception_class' => $exception::class,
            'error_code' => $exception->getCode(),
        ];
    }
}
