<?php

use App\Http\Controllers\ContactController;
use App\Http\Controllers\SubscribeController;
use App\Http\Controllers\WaitlistController;
use Illuminate\Support\Facades\Route;

Route::view('/', 'pages.home')->name('home');
Route::view('/chi-siamo', 'pages.about')->name('about');
Route::view('/contatti', 'pages.contact')->name('contact');
Route::get('/subscribe', [SubscribeController::class, 'show'])->name('subscribe');
Route::get('/checkout/success', [SubscribeController::class, 'success'])->name('checkout.success');
Route::post('/subscribe/access', [SubscribeController::class, 'access'])->middleware('throttle:waitlist')->name('subscribe.access');
Route::post('/subscribe/checkout', [SubscribeController::class, 'checkout'])->middleware('throttle:checkout')->name('subscribe.checkout');
Route::post('/purchase/confirmation', [SubscribeController::class, 'resendPurchaseConfirmation'])->middleware('throttle:purchase-confirmation')->name('purchase.confirmation.resend');
Route::post('/contatti', [ContactController::class, 'store'])->middleware('throttle:contact')->name('contact.store');
Route::post('/waitlist', [WaitlistController::class, 'store'])->middleware('throttle:waitlist')->name('waitlist.store');
