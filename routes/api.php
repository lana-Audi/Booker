<?php

use App\Http\Controllers\ApartmentController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ForgotPasswordController;
use App\Http\Controllers\AdminController;


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
Route::post('Apartmentregister', [ApartmentController::class, 'store']);
Route::get('index', [ApartmentController::class, 'index']);
Route::middleware(['auth:sanctum'])->group(function () {
Route::post('logout', [UserController::class, 'logout']);
});


Route::post('/forgot-password/send-otp', [ForgotPasswordController::class, 'sendOtp']);
Route::post('/forgot-password/verify-otp', [ForgotPasswordController::class, 'verifyOtp']);
Route::post('/forgot-password/reset', [ForgotPasswordController::class, 'resetPassword']);




Route::middleware('auth:sanctum')->group(function () {
    Route::get('/admin/pending-users', [AdminController::class, 'pendingUsers']);
    Route::post('/admin/approve-user/{id}', [AdminController::class, 'approveUser']);
});
