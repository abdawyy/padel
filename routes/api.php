<?php

use App\Http\Controllers\Api\AcademySessionController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\AvailabilityController;
use App\Http\Controllers\Api\BookingController;
use App\Http\Controllers\Api\ClubController;
use App\Http\Controllers\Api\ClubRegistrationController;
use App\Http\Controllers\Api\ClubStaffController;
use App\Http\Controllers\Api\CoachApplicationController;
use App\Http\Controllers\Api\CourtController;
use App\Http\Controllers\Api\CourtSlotController;
use App\Http\Controllers\Api\MatchmakingController;
use App\Http\Controllers\Api\PackageController;
use App\Http\Controllers\Api\PaymentController;
use App\Http\Controllers\Api\PlayerAvailabilityController;
use App\Http\Controllers\Api\SaasPlanController;
use App\Http\Controllers\Api\WebhookController;
use Illuminate\Support\Facades\Route;

Route::post('register', [AuthController::class, 'register'])->middleware('throttle:10,1');
Route::post('login', [AuthController::class, 'login'])->middleware('throttle:10,1');
Route::post('forgot-password', [AuthController::class, 'forgotPassword'])->middleware('throttle:6,1');
Route::post('reset-password', [AuthController::class, 'resetPassword'])->middleware('throttle:6,1');
Route::get('email/verify/{id}/{hash}', [AuthController::class, 'verifyEmail'])
    ->middleware(['signed', 'throttle:6,1'])
    ->name('api.verification.verify');
Route::post('webhooks/paymob/transaction-processed', [WebhookController::class, 'transactionProcessed'])
    ->middleware('paymob.webhook');

Route::get('academy-sessions', [AcademySessionController::class, 'publicIndex']);
Route::get('academy-sessions/{academySession}', [AcademySessionController::class, 'show']);
Route::get('matches/open', [MatchmakingController::class, 'index']);
Route::get('saas-plans', [SaasPlanController::class, 'index']);
Route::apiResource('clubs', ClubController::class)->only(['index', 'show']);
Route::get('clubs/{club}/sport-rules/{sport}', [ClubController::class, 'sportRules'])
    ->middleware('throttle:30,1');
Route::apiResource('courts', CourtController::class)->only(['index', 'show']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('logout', [AuthController::class, 'logout']);
    Route::post('user/password', [AuthController::class, 'changePassword']);
    Route::post('email/verification-notification', [AuthController::class, 'resendVerificationEmail'])
        ->middleware('throttle:6,1');

    Route::middleware('verified.api')->group(function () {
        Route::post('register-club', [ClubRegistrationController::class, 'register']);
        Route::post('clubs/{club}/saas-subscription', [ClubRegistrationController::class, 'renew']);
        Route::post('bookings', [BookingController::class, 'store']);
        Route::post('bookings/{booking}/pay', [PaymentController::class, 'pay']);
        Route::post('bookings/{booking}/join', [MatchmakingController::class, 'join']);
        Route::post('academy-sessions/{academySession}/enroll', [AcademySessionController::class, 'enroll']);
        Route::get('clubs/{club}/packages', [PackageController::class, 'index']);
        Route::post('clubs/{club}/packages/subscribe', [PackageController::class, 'subscribe']);
    });

    Route::get('clubs/{club}/player-availability', [PlayerAvailabilityController::class, 'show']);
    Route::get('user/bookings', [BookingController::class, 'userBookings']);
    Route::get('user/academy-sessions', [AcademySessionController::class, 'mySessions']);
    Route::get('clubs/{club}/saas-subscription', [ClubRegistrationController::class, 'show']);
    Route::get('clubs/{club}/availability', [AvailabilityController::class, 'index']);
    Route::get('clubs/{club}/staff', [ClubStaffController::class, 'index']);
    Route::post('clubs/{club}/staff', [ClubStaffController::class, 'store']);
    Route::put('clubs/{club}/staff/{user}', [ClubStaffController::class, 'update']);
    Route::delete('clubs/{club}/staff/{user}', [ClubStaffController::class, 'destroy']);
    Route::get('clubs/{club}/slots', [CourtSlotController::class, 'index']);
    Route::post('clubs/{club}/slots', [CourtSlotController::class, 'store']);
    Route::post('clubs/{club}/slots/{courtSlot}/schedule', [CourtSlotController::class, 'schedule']);
    Route::get('clubs/{club}/academy-sessions', [AcademySessionController::class, 'index']);
    Route::post('clubs/{club}/academy-sessions', [AcademySessionController::class, 'store']);
    Route::post('academy-sessions/{academySession}/coach-apply', [CoachApplicationController::class, 'apply']);
    Route::delete('coach-applications/{coachApplication}', [CoachApplicationController::class, 'withdraw']);
    Route::get('academy-sessions/{academySession}/coach-applications', [CoachApplicationController::class, 'index']);
    Route::patch('coach-applications/{coachApplication}', [CoachApplicationController::class, 'respond']);
    Route::patch('academy-sessions/{academySession}', [AcademySessionController::class, 'update']);
    Route::post('academy-sessions/{academySession}/cancel', [AcademySessionController::class, 'cancel']);
    Route::put('slots/{courtSlot}', [CourtSlotController::class, 'update']);
    Route::delete('slots/{courtSlot}', [CourtSlotController::class, 'destroy']);
    Route::apiResource('clubs', ClubController::class)->except(['index', 'show', 'store']);
    Route::apiResource('courts', CourtController::class)->except(['index', 'show']);
    Route::apiResource('bookings', BookingController::class)->except(['store']);
    Route::post('bookings/{booking}/cancel', [BookingController::class, 'cancel']);
    Route::post('bookings/{booking}/leave', [BookingController::class, 'leave']);
});
