<?php

use App\Http\Controllers\ApartmentController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ForgotPasswordController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\RatingController;
use App\Http\Controllers\ReservationController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

//profile
Route::middleware('auth:sanctum')->group(function () {

    Route::prefix('profile')->group(function () {
        Route::Post('', [ProfileController::class, 'store']);
        Route::get('/{id}', [ProfileController::class, 'show']);
        Route::put('/{id}', [ProfileController::class, 'update']);
    });
});

//user
Route::post('register', [UserController::class,'register']);
Route::post('/verify-otp', [UserController::class, 'verifyOtp']);
Route::post('login', [UserController::class, 'login']);
Route::middleware(['auth:sanctum'])->group(function () {
Route::post('logout', [UserController::class, 'logout']);
});


//للشقة 
Route::middleware(['auth:sanctum'])->group(function () {
Route::post('Apartmentregister', [ApartmentController::class, 'store']);
});
Route::get('index', [ApartmentController::class, 'index']);


//للباسورد
Route::post('/forgot-password/send-otp', [ForgotPasswordController::class, 'sendOtp']);
Route::post('/forgot-password/verify-otp', [ForgotPasswordController::class, 'verifyOtp']);
Route::post('/forgot-password/reset', [ForgotPasswordController::class, 'resetPassword']);



//للادمن
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/admin/pending-users', [AdminController::class, 'pendingUsers']);
    Route::post('/admin/approve-user/{id}', [AdminController::class, 'approveUser']);
});

//للحجز
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/book', [ReservationController::class, 'book']);
    Route::get('/my-reservations', [ReservationController::class, 'getUserReservations']);
    Route::put('/cancel-reservation/{id}', [ReservationController::class, 'cancelReservation']);
    Route::put('/update_book/{id}', [ReservationController::class, 'cancelReservation']);

});


//لتقييم الشقق
Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('/rate{id}', [RatingController::class, 'create']);
    Route::post('/bookings/{id}/rate', [RatingController::class, 'store']);
    });

    
Route::middleware(['auth:sanctum'])->group(function () {
    Route::post('/filter', [ApartmentController::class, 'filter']);
    });