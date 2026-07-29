<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AdminAuthController;
use App\Http\Controllers\AdminUserController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\ConsentController;
use App\Http\Controllers\LegalDocumentController;
use App\Http\Controllers\SubscribeController;
use App\Http\Controllers\WaitlistController;
use App\Http\Controllers\WithdrawalController;
use Illuminate\Support\Facades\Route;

Route::view('/', 'pages.home')->name('home');
Route::view('/chi-siamo', 'pages.about')->name('about');
Route::view('/contatti', 'pages.contact')->name('contact');
Route::get('/privacy-policy', [LegalDocumentController::class, 'show'])->defaults('document', 'privacy')->name('legal.privacy');
Route::get('/cookie-policy', [LegalDocumentController::class, 'show'])->defaults('document', 'cookies')->name('legal.cookies');
Route::get('/termini-e-condizioni', [LegalDocumentController::class, 'show'])->defaults('document', 'terms')->name('legal.terms');
Route::get('/come-funzionano-i-pass', [LegalDocumentController::class, 'show'])->defaults('document', 'passes')->name('legal.passes');
Route::get('/termini-di-vendita', [LegalDocumentController::class, 'show'])->defaults('document', 'sales')->name('legal.sales');
Route::redirect('/condizioni-di-vendita', '/termini-di-vendita', 301);
Route::get('/condizioni-di-pre-sale', [LegalDocumentController::class, 'show'])->defaults('document', 'presale')->name('legal.presale');
Route::get('/recedere-dal-contratto', [WithdrawalController::class, 'create'])->name('legal.refunds');
Route::get('/documenti/modulo-tipo-recesso', [WithdrawalController::class, 'downloadTemplate'])->name('withdrawal.template.download');
Route::post('/recedere-dal-contratto/verifica', [WithdrawalController::class, 'storeReview'])->middleware('throttle:withdrawal')->name('withdrawal.review.store');
Route::get('/recedere-dal-contratto/conferma', [WithdrawalController::class, 'review'])->name('withdrawal.review');
Route::post('/recedere-dal-contratto/conferma', [WithdrawalController::class, 'confirm'])->middleware('throttle:withdrawal-confirm')->name('withdrawal.confirm');
Route::get('/recedere-dal-contratto/ricevuta/{token}', [WithdrawalController::class, 'receipt'])->name('withdrawal.receipt');
Route::get('/recedere-dal-contratto/ricevuta/{token}/download', [WithdrawalController::class, 'download'])->name('withdrawal.receipt.download');
Route::redirect('/recesso-e-rimborso', '/recedere-dal-contratto', 301);
Route::get('/note-legali', [LegalDocumentController::class, 'show'])->defaults('document', 'notice')->name('legal.notice');
Route::get('/subscribe', [SubscribeController::class, 'show'])->name('subscribe');
Route::get('/checkout/success', [SubscribeController::class, 'success'])->name('checkout.success');
Route::post('/subscribe/access', [SubscribeController::class, 'access'])->middleware('throttle:waitlist')->name('subscribe.access');
Route::post('/subscribe/checkout', [SubscribeController::class, 'checkout'])->middleware('throttle:checkout')->name('subscribe.checkout');
Route::post('/purchase/confirmation', [SubscribeController::class, 'resendPurchaseConfirmation'])->middleware('throttle:purchase-confirmation')->name('purchase.confirmation.resend');
Route::post('/contatti', [ContactController::class, 'store'])->middleware('throttle:contact')->name('contact.store');
Route::post('/waitlist', [WaitlistController::class, 'store'])->middleware('throttle:waitlist')->name('waitlist.store');
Route::post('/waitlist/profile', [WaitlistController::class, 'completeProfile'])->middleware('throttle:waitlist')->name('waitlist.profile');
Route::get('/preferenze/marketing/{waitlist}', [ConsentController::class, 'showMarketingRevocation'])
    ->middleware('signed')
    ->name('consent.marketing.revoke.show');
Route::post('/preferenze/marketing/{waitlist}', [ConsentController::class, 'revokeMarketing'])
    ->middleware(['signed', 'throttle:waitlist'])
    ->name('consent.marketing.revoke');

Route::get('/admin/login', [AdminAuthController::class, 'login'])->middleware('throttle:admin')->name('admin.login');
Route::post('/admin/login', [AdminAuthController::class, 'authenticate'])->middleware('throttle:admin')->name('admin.authenticate');
Route::get('/admin/otp/setup', [AdminAuthController::class, 'showSetup'])->middleware('throttle:admin')->name('admin.otp.setup');
Route::post('/admin/otp/setup', [AdminAuthController::class, 'confirmSetup'])->middleware('throttle:admin')->name('admin.otp.setup.confirm');
Route::get('/admin/otp/challenge', [AdminAuthController::class, 'showChallenge'])->middleware('throttle:admin')->name('admin.otp.challenge');
Route::post('/admin/otp/challenge', [AdminAuthController::class, 'verifyChallenge'])->middleware('throttle:admin')->name('admin.otp.verify');

Route::middleware('admin.auth')->group(function () {
    Route::post('/admin/logout', [AdminAuthController::class, 'logout'])->name('admin.logout');
    Route::get('/admin/recovery-codes', [AdminAuthController::class, 'recoveryCodes'])->name('admin.recovery-codes');
    Route::post('/admin/recovery-codes', [AdminAuthController::class, 'acknowledgeRecoveryCodes'])->name('admin.recovery-codes.acknowledge');
    Route::post('/admin/security/reset-otp', [AdminAuthController::class, 'resetOwnOtp'])->middleware('throttle:admin')->name('admin.otp.reset-own');
    Route::post('/admin/security/password', [AdminAuthController::class, 'changePassword'])->middleware('throttle:admin')->name('admin.password.change');
    Route::post('/admin/users', [AdminUserController::class, 'store'])->name('admin.users.store');
    Route::post('/admin/settings', [AdminController::class, 'updateSettings'])->name('admin.settings.update');
    Route::post('/admin/legal-documents/{document}', [AdminController::class, 'publishLegalDocument'])->name('admin.legal-documents.publish');
    Route::get('/admin', [AdminController::class, 'dashboard'])->name('admin.dashboard');
});
