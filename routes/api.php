<?php

use App\Http\Controllers\Api\PrelaunchBenefitController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1/prelaunch')
    ->middleware(['benefit.api', 'throttle:benefit-api'])
    ->group(function (): void {
        Route::post('/benefits/claim', [PrelaunchBenefitController::class, 'claim']);
        Route::post('/benefits/email-challenges', [PrelaunchBenefitController::class, 'createEmailChallenge']);
        Route::post('/benefits/email-challenges/verify', [PrelaunchBenefitController::class, 'verifyEmailChallenge']);
    });
