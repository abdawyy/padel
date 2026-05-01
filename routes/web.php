<?php

use App\Http\Controllers\Web\AcademyRegistrationController;
use Illuminate\Support\Facades\Route;

Route::get('/', [AcademyRegistrationController::class, 'home'])->name('home');
Route::get('/register-academy', [AcademyRegistrationController::class, 'showRegister'])->name('register.academy');
Route::post('/register-academy', [AcademyRegistrationController::class, 'register'])->name('register.academy.submit');
Route::get('/register-academy/pending', [AcademyRegistrationController::class, 'pending'])->name('register.pending');
