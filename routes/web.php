<?php

use App\Http\Controllers\Web\AcademyRegistrationController;
use App\Http\Controllers\Web\PasswordResetController;
use Illuminate\Support\Facades\Route;

Route::get('/', [AcademyRegistrationController::class, 'home'])->name('home');
Route::get('/register-academy', [AcademyRegistrationController::class, 'showRegister'])->name('register.academy');
Route::post('/register-academy', [AcademyRegistrationController::class, 'register'])->name('register.academy.submit');
Route::get('/register-academy/pending', [AcademyRegistrationController::class, 'pending'])->name('register.pending');
Route::get('/register-academy/payment', [AcademyRegistrationController::class, 'payment'])->name('register.payment');

Route::get('/forgot-password', [PasswordResetController::class, 'showForgotForm'])->name('password.request');
Route::post('/forgot-password', [PasswordResetController::class, 'sendResetLink'])->name('password.email');
Route::get('/reset-password/{token}', [PasswordResetController::class, 'showResetForm'])->name('password.reset');
Route::post('/reset-password', [PasswordResetController::class, 'resetPassword'])->name('password.update');

Route::post('/player/locale', function (\Illuminate\Http\Request $request) {
    $locale = $request->validate(['locale' => 'required|in:en,ar'])['locale'];
    $request->session()->put('player_locale', $locale);

    return back();
})->middleware('web')->name('player.locale');
