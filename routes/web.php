<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\SubscribeController;
use App\Http\Controllers\WaitlistController;
use Illuminate\Support\Facades\Route;

Route::view('/', 'pages.home')->name('home');
Route::view('/chi-siamo', 'pages.about')->name('about');
Route::view('/contatti', 'pages.contact')->name('contact');
Route::view('/privacy-policy', 'pages.legal.privacy')->name('legal.privacy');
Route::view('/cookie-policy', 'pages.legal.cookies')->name('legal.cookies');
Route::view('/termini-e-condizioni', 'pages.legal.terms')->name('legal.terms');
Route::view('/come-funzionano-i-pass', 'pages.legal.passes')->name('legal.passes');
Route::view('/condizioni-di-vendita', 'pages.legal.sales')->name('legal.sales');
Route::view('/recesso-e-rimborso', 'pages.legal.refunds')->name('legal.refunds');
Route::view('/note-legali', 'pages.legal.notice')->name('legal.notice');
Route::get('/subscribe', [SubscribeController::class, 'show'])->name('subscribe');
Route::get('/checkout/success', [SubscribeController::class, 'success'])->name('checkout.success');
Route::post('/subscribe/access', [SubscribeController::class, 'access'])->middleware('throttle:waitlist')->name('subscribe.access');
Route::post('/subscribe/checkout', [SubscribeController::class, 'checkout'])->middleware('throttle:checkout')->name('subscribe.checkout');
Route::post('/purchase/confirmation', [SubscribeController::class, 'resendPurchaseConfirmation'])->middleware('throttle:purchase-confirmation')->name('purchase.confirmation.resend');
Route::post('/contatti', [ContactController::class, 'store'])->middleware('throttle:contact')->name('contact.store');
Route::post('/waitlist', [WaitlistController::class, 'store'])->middleware('throttle:waitlist')->name('waitlist.store');
Route::post('/waitlist/profile', [WaitlistController::class, 'completeProfile'])->middleware('throttle:waitlist')->name('waitlist.profile');

Route::get('/admin/login', [AdminController::class, 'login'])->middleware('throttle:admin')->name('admin.login');
Route::post('/admin/login', [AdminController::class, 'authenticate'])->middleware('throttle:admin')->name('admin.authenticate');
Route::post('/admin/logout', [AdminController::class, 'logout'])->name('admin.logout');
Route::post('/admin/settings', [AdminController::class, 'updateSettings'])->name('admin.settings.update');
Route::get('/admin', [AdminController::class, 'dashboard'])->name('admin.dashboard');
