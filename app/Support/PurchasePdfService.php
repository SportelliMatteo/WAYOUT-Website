<?php

namespace App\Support;

use Barryvdh\DomPDF\Facade\Pdf;

class PurchasePdfService
{
    public function orderSummary(object $purchase): string
    {
        return Pdf::loadView('pdf.order-summary', [
            'purchase' => $purchase,
            'planName' => $purchase->plan === 'creator'
                ? 'Founder 12M Creator Pass'
                : 'Founder Join 12M Pass',
        ])->setPaper('a4')->output();
    }
}
