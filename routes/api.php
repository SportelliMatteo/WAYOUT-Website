<?php

use App\Http\Controllers\Api\PrelaunchBenefitController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1/prelaunch')
    ->middleware(['benefit.api', 'throttle:benefit-api'])
    ->group(function (): void {
        Route::get('/benefits', [PrelaunchBenefitController::class, 'index']);
        Route::post('/benefits/eligibility', [PrelaunchBenefitController::class, 'eligibility']);
    });
