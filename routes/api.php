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
use App\Http\Controllers\Api\PaymentController;
use App\Http\Controllers\Api\SaasPlanController;
use App\Http\Controllers\Api\WebhookController;
use Illuminate\Support\Facades\Route;

Route::post('register', [AuthController::class, 'register']);
Route::post('login', [AuthController::class, 'login']);
Route::post('webhooks/paymob/transaction-processed', [WebhookController::class, 'transactionProcessed']);

Route::get('academy-sessions', [AcademySessionController::class, 'publicIndex']);
Route::get('academy-sessions/{academySession}', [AcademySessionController::class, 'show']);
Route::get('matches/open', [MatchmakingController::class, 'index']);
Route::get('saas-plans', [SaasPlanController::class, 'index']);
Route::apiResource('clubs', ClubController::class)->only(['index', 'show']);
Route::get('clubs/{club}/sport-rules/{sport}', [ClubController::class, 'sportRules']);
Route::apiResource('courts', CourtController::class)->only(['index', 'show']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('logout', [AuthController::class, 'logout']);
    Route::post('register-club', [ClubRegistrationController::class, 'register']);
    Route::get('clubs/{club}/saas-subscription', [ClubRegistrationController::class, 'show']);
    Route::post('clubs/{club}/saas-subscription', [ClubRegistrationController::class, 'renew']);
    Route::get('user/bookings', [BookingController::class, 'userBookings']);
    Route::get('user/academy-sessions', [AcademySessionController::class, 'mySessions']);
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
    Route::post('academy-sessions/{academySession}/enroll', [AcademySessionController::class, 'enroll']);
    Route::post('academy-sessions/{academySession}/coach-apply', [CoachApplicationController::class, 'apply']);
    Route::delete('coach-applications/{coachApplication}', [CoachApplicationController::class, 'withdraw']);
    Route::get('academy-sessions/{academySession}/coach-applications', [CoachApplicationController::class, 'index']);
    Route::patch('coach-applications/{coachApplication}', [CoachApplicationController::class, 'respond']);
    Route::put('slots/{courtSlot}', [CourtSlotController::class, 'update']);
    Route::delete('slots/{courtSlot}', [CourtSlotController::class, 'destroy']);
    Route::apiResource('clubs', ClubController::class)->except(['index', 'show']);
    Route::apiResource('courts', CourtController::class)->except(['index', 'show']);
    Route::apiResource('bookings', BookingController::class);
    Route::post('bookings/{booking}/pay', [PaymentController::class, 'pay']);
    Route::post('bookings/{booking}/join', [MatchmakingController::class, 'join']);
});
