<?php

namespace App\Support;

class PurchasePackage
{
    public static function name(object $purchase): string
    {
        return $purchase->package_name ?? match ($purchase->plan) {
            'join' => 'Founder Join 12M Pass',
            'creator' => 'Founder 12M Creator Pass',
            default => $purchase->promo_package_code ?? $purchase->plan,
        };
    }
}
