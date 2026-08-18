<?php

namespace App\Support;

final class ItalianFiscalData
{
    public static function normalizeVatNumber(?string $value, ?string $country = 'IT'): ?string
    {
        $value = strtoupper(preg_replace('/[^A-Z0-9]/', '', (string) $value) ?? '');

        if ($value === '') {
            return null;
        }

        if (strtoupper((string) $country) === 'IT' && str_starts_with($value, 'IT')) {
            return substr($value, 2);
        }

        return $value;
    }

    public static function qontoVatNumber(?string $value, ?string $country = 'IT'): ?string
    {
        $normalized = self::normalizeVatNumber($value, $country);

        if ($normalized === null) {
            return null;
        }

        return strtoupper((string) $country) === 'IT' ? 'IT'.$normalized : $normalized;
    }

    public static function isValidVatNumber(?string $value, ?string $country = 'IT'): bool
    {
        $normalized = self::normalizeVatNumber($value, $country);

        if (strtoupper((string) $country) !== 'IT') {
            return is_string($normalized) && preg_match('/^[A-Z0-9]{8,20}$/', $normalized) === 1;
        }

        if (! is_string($normalized) || preg_match('/^\d{11}$/', $normalized) !== 1) {
            return false;
        }

        $sum = 0;

        for ($index = 0; $index < 10; $index++) {
            $digit = (int) $normalized[$index];

            if ($index % 2 === 0) {
                $sum += $digit;
            } else {
                $doubled = $digit * 2;
                $sum += $doubled > 9 ? $doubled - 9 : $doubled;
            }
        }

        return (10 - ($sum % 10)) % 10 === (int) $normalized[10];
    }

    public static function isValidFiscalCode(?string $value): bool
    {
        $value = strtoupper(preg_replace('/\s+/', '', (string) $value) ?? '');

        if (preg_match('/^[A-Z0-9]{16}$/', $value) !== 1) {
            return false;
        }

        $oddValues = [
            '0' => 1, '1' => 0, '2' => 5, '3' => 7, '4' => 9, '5' => 13, '6' => 15, '7' => 17, '8' => 19, '9' => 21,
            'A' => 1, 'B' => 0, 'C' => 5, 'D' => 7, 'E' => 9, 'F' => 13, 'G' => 15, 'H' => 17, 'I' => 19, 'J' => 21,
            'K' => 2, 'L' => 4, 'M' => 18, 'N' => 20, 'O' => 11, 'P' => 3, 'Q' => 6, 'R' => 8, 'S' => 12, 'T' => 14,
            'U' => 16, 'V' => 10, 'W' => 22, 'X' => 25, 'Y' => 24, 'Z' => 23,
        ];
        $evenValues = array_merge(
            array_combine(range('0', '9'), range(0, 9)),
            array_combine(range('A', 'Z'), range(0, 25)),
        );
        $sum = 0;

        for ($index = 0; $index < 15; $index++) {
            $sum += $index % 2 === 0
                ? $oddValues[$value[$index]]
                : $evenValues[$value[$index]];
        }

        return chr(($sum % 26) + ord('A')) === $value[15];
    }
}
