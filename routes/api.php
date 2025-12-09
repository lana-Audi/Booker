<?php

use App\Http\Controllers\ApartmentController;
use App\Http\Controllers\ProfilController;
use App\Http\Controllers\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

//profile
Route::middleware('auth:sanctum')->group(function () {

    Route::prefix('profile')->group(function () {
        Route::Post('', [ProfilController::class, 'store']);
        Route::get('/{id}', [ProfilController::class, 'show']);
        Route::put('/{id}', [ProfilController::class, 'update']);
    });
});

//user
Route::post('register', [UserController::class, 'register']);
Route::post('login', [UserController::class, 'login']);
Route::post('Apartmentregister', [ApartmentController::class, 'store']);
Route::get('index', [ApartmentController::class, 'index']);
Route::middleware(['auth:sanctum'])->group(function () {
Route::post('logout', [UserController::class, 'logout']);
});
