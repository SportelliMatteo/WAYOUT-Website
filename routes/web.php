<?php

use App\Http\Controllers\AccountDeletionController;
use App\Http\Controllers\AdminAuthController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\AdminUserController;
use App\Http\Controllers\CommerceSupportController;
use App\Http\Controllers\ConsentController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\CookieConsentController;
use App\Http\Controllers\CustomerAccountController;
use App\Http\Controllers\FaqController;
use App\Http\Controllers\LegalDocumentController;
use App\Http\Controllers\SubscribeController;
use App\Http\Controllers\SubscriptionCancellationController;
use App\Http\Controllers\WaitlistController;
use App\Http\Controllers\WayoutCheckoutController;
use App\Http\Controllers\WithdrawalController;
use Illuminate\Support\Facades\Route;

Route::view('/', 'pages.home')->name('home');
Route::view('/chi-siamo', 'pages.about')->name('about');
Route::view('/contatti', 'pages.contact')->name('contact');
Route::get('/faq', FaqController::class)->name('faq');
Route::view('/api/docs', 'api-docs')->name('api.docs');
Route::get('/documenti-legali/{scope}/{lang}', [LegalDocumentController::class, 'index'])
    ->where(['scope' => 'site|app', 'lang' => 'it'])->name('legal.index');
Route::prefix('/documenti-legali/{document}/{scope}/{lang}')
    ->where(['document' => '[a-z][a-z0-9]*(?:-[a-z0-9]+)*', 'scope' => 'site|app', 'lang' => 'it'])
    ->group(function () {
        Route::get('/', [LegalDocumentController::class, 'show'])->name('legal.document');
        Route::get('/versioni', [LegalDocumentController::class, 'history'])->name('legal.history');
        Route::get('/versioni/{version}', [LegalDocumentController::class, 'version'])->whereUuid('version')->name('legal.version');
    });
Route::get('/documenti-legali', [LegalDocumentController::class, 'index']);
// Existing URLs remain readable; all newly generated links use the canonical paths.
Route::get('/documenti-legali/{document}', [LegalDocumentController::class, 'show']);
Route::get('/documenti-legali/{document}/versioni', [LegalDocumentController::class, 'history']);
Route::get('/documenti-legali/{document}/versioni/{version}', [LegalDocumentController::class, 'version'])->whereUuid('version');
Route::get('/privacy-policy', [LegalDocumentController::class, 'show'])->defaults('document', 'privacy')->name('legal.privacy');
Route::get('/cookie-policy', [LegalDocumentController::class, 'show'])->defaults('document', 'cookies')->name('legal.cookies');
Route::get('/termini-e-condizioni', [LegalDocumentController::class, 'show'])->defaults('document', 'terms')->name('legal.terms');
Route::get('/come-funzionano-i-pass', [LegalDocumentController::class, 'show'])->defaults('document', 'passes')->name('legal.passes');
Route::get('/termini-di-vendita', [LegalDocumentController::class, 'show'])->defaults('document', 'sales')->name('legal.sales');
Route::redirect('/condizioni-di-vendita', '/termini-di-vendita', 301);
Route::get('/condizioni-di-pre-sale', [LegalDocumentController::class, 'show'])->defaults('document', 'presale')->name('legal.presale');
Route::get('/recedere-dal-contratto', [WithdrawalController::class, 'create'])->name('legal.refunds');
Route::get('/documenti/modulo-tipo-recesso', [WithdrawalController::class, 'downloadTemplate'])->name('withdrawal.template.download');
Route::post('/recedere-dal-contratto/verifica', [CommerceSupportController::class, 'withdrawal'])->middleware(['throttle:withdrawal', 'recaptcha:withdrawal_review'])->name('withdrawal.review.store');
Route::redirect('/recedere-dal-contratto/conferma', '/recedere-dal-contratto', 303)->name('withdrawal.review');
Route::post('/recedere-dal-contratto/conferma', fn () => redirect()->route('legal.refunds', status: 303))->name('withdrawal.confirm');
Route::get('/recedere-dal-contratto/ricevuta/{token}', [WithdrawalController::class, 'receipt'])->where('token', '[A-Za-z0-9]{64}')->name('withdrawal.receipt');
Route::get('/recedere-dal-contratto/ricevuta/{token}/download', [WithdrawalController::class, 'download'])->where('token', '[A-Za-z0-9]{64}')->name('withdrawal.receipt.download');
Route::redirect('/recesso-e-rimborso', '/recedere-dal-contratto', 301);
Route::get('/disdetta-abbonamento', [SubscriptionCancellationController::class, 'create'])->name('subscription.cancel');
Route::redirect('/cancellazione-account', '/richiesta-cancellazione-account-e-dati-associati', 301);
Route::get('/richiesta-cancellazione-account-e-dati-associati', [AccountDeletionController::class, 'create'])->name('account.deletion');
Route::post('/richiesta-cancellazione-account-e-dati-associati', [AccountDeletionController::class, 'store'])->middleware(['throttle:purchase-support', 'recaptcha:account_deletion'])->name('account.deletion.store');
Route::post('/disdetta-abbonamento', [CommerceSupportController::class, 'cancellation'])->middleware(['throttle:purchase-support', 'recaptcha:subscription_cancel'])->name('subscription.cancel.store');
Route::get('/note-legali', [LegalDocumentController::class, 'show'])->defaults('document', 'notice')->name('legal.notice');
Route::middleware('plans.enabled')->group(function () {
    Route::get('/accedi', [CustomerAccountController::class, 'login'])->name('customer.login');
    Route::post('/accedi', [CustomerAccountController::class, 'authenticate'])->middleware('throttle:10,1')->name('customer.authenticate');
    Route::post('/esci', [CustomerAccountController::class, 'logout'])->name('customer.logout');
    Route::post('/area-riservata/gestisci-abbonamento', [CustomerAccountController::class, 'portal'])->middleware(['customer.auth', 'throttle:checkout'])->name('customer.portal');
    Route::get('/area-riservata/piani', [WayoutCheckoutController::class, 'plans'])->middleware(['customer.auth', 'throttle:checkout'])->name('customer.plans');
    Route::get('/area-riservata', [CustomerAccountController::class, 'dashboard'])->middleware('customer.auth')->name('customer.dashboard');

    Route::get('/piani', [SubscribeController::class, 'show'])->name('subscribe');
    Route::redirect('/subscribe', '/piani', 301);
    Route::get('/checkout/success/{purchase}', [SubscribeController::class, 'success'])->middleware(['customer.auth', 'signed'])->name('checkout.success');
    Route::get('/checkout/status/{purchase}', [SubscribeController::class, 'status'])->middleware(['customer.auth', 'signed', 'throttle:checkout'])->name('checkout.status');
    Route::get('/checkout/cancel/{purchase}', [WayoutCheckoutController::class, 'cancel'])->middleware(['customer.auth', 'signed'])->name('checkout.cancel');
    Route::get('/subscribe/plans', [WayoutCheckoutController::class, 'plans'])->middleware('throttle:checkout')->name('subscribe.plans');
    Route::post('/subscribe/checkout', [WayoutCheckoutController::class, 'store'])->middleware(['customer.auth', 'throttle:checkout'])->name('subscribe.checkout');
    Route::post('/purchase/confirmation', [SubscribeController::class, 'resendPurchaseConfirmation'])->middleware(['customer.auth', 'throttle:purchase-confirmation', 'recaptcha:purchase_confirmation'])->name('purchase.confirmation.resend');
});
Route::post('/contatti', [ContactController::class, 'store'])->middleware(['throttle:contact', 'recaptcha:contact'])->name('contact.store');
Route::post('/analytics/lead', [WaitlistController::class, 'trackLead'])->middleware('throttle:30,1')->name('analytics.lead');
Route::post('/cookie-consent', [CookieConsentController::class, 'store'])->middleware('throttle:120,1')->name('cookie-consent.store');
Route::get('/preferenze/marketing/{waitlist}', [ConsentController::class, 'showMarketingRevocation'])
    ->middleware('signed')
    ->name('consent.marketing.revoke.show');
Route::post('/preferenze/marketing/{waitlist}', [ConsentController::class, 'revokeMarketing'])
    ->middleware(['signed', 'throttle:waitlist'])
    ->name('consent.marketing.revoke');

Route::get('/admin/login', [AdminAuthController::class, 'login'])->middleware('throttle:admin')->name('admin.login');
Route::post('/admin/login', [AdminAuthController::class, 'authenticate'])->middleware(['throttle:admin', 'recaptcha:admin_login'])->name('admin.authenticate');
Route::post('/admin/recover', [AdminAuthController::class, 'recover'])->middleware(['throttle:admin', 'recaptcha:admin_recover'])->name('admin.recover');
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
    Route::post('/admin/site-visibility', [AdminController::class, 'updateSiteVisibility'])->name('admin.site-visibility.update');
    Route::post('/admin/site-preview', [AdminController::class, 'enableSitePreview'])->middleware('throttle:admin')->name('admin.site-preview.enable');
    Route::delete('/admin/site-preview', [AdminController::class, 'disableSitePreview'])->middleware('throttle:admin')->name('admin.site-preview.disable');
    Route::post('/admin/app-store-links', [AdminController::class, 'updateAppStoreLinks'])->name('admin.app-store-links.update');
    Route::post('/admin/checkout-settings', [AdminController::class, 'updateCheckoutSettings'])->name('admin.checkout-settings.update');
    Route::post('/admin/plans-availability', [AdminController::class, 'updatePlansAvailability'])->name('admin.plans-availability.update');
    Route::get('/admin', [AdminController::class, 'dashboard'])->name('admin.dashboard');
});
