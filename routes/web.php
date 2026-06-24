<?php

use App\Http\Controllers\ContactController;
use App\Http\Controllers\SubscribeController;
use App\Http\Controllers\WaitlistController;
use Illuminate\Support\Facades\Route;

Route::view('/', 'pages.home')->name('home');
Route::view('/chi-siamo', 'pages.about')->name('about');
Route::view('/contatti', 'pages.contact')->name('contact');
Route::view('/subscribe', 'pages.subscribe')->name('subscribe');
Route::post('/subscribe/checkout', [SubscribeController::class, 'checkout'])->name('subscribe.checkout');
Route::post('/contatti', [ContactController::class, 'store'])->name('contact.store');
Route::post('/waitlist', [WaitlistController::class, 'store'])->name('waitlist.store');
