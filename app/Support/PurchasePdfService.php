<?php

namespace App\Support;

use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\App;

class PurchasePdfService
{
    public function orderSummary(object $purchase, string $locale = 'it'): string
    {
        $previousLocale = App::getLocale();

        try {
            App::setLocale($locale);

            return Pdf::loadView('pdf.order-summary', [
                'purchase' => $purchase,
                'planName' => PurchasePackage::name($purchase),
            ])->setPaper('a4')->output();
        } finally {
            App::setLocale($previousLocale);
        }
    }
}
