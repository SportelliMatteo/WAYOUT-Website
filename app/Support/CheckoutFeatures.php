<?php

namespace App\Support;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Throwable;

final class CheckoutFeatures
{
    public const LEGAL_ENTITY_INVOICE_KEY = 'legal_entity_invoice_enabled';

    public function legalEntityInvoiceEnabled(): bool
    {
        try {
            return Schema::hasTable('founder_settings')
                && (int) DB::table('founder_settings')
                    ->where('key', self::LEGAL_ENTITY_INVOICE_KEY)
                    ->value('value') === 1;
        } catch (Throwable) {
            return false;
        }
    }
}
