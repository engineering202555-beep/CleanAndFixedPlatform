<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Customer\AuthController;
Route::middleware(['auth:sanctum', 'role:customer'])->group(function () {

});
Route::prefix('auth')->group(function () {

    Route::post('/register', [AuthController::class, 'register']);

    Route::post('/verify-otp', [AuthController::class, 'verifyOtp']);

    Route::post('/resend-otp', [AuthController::class, 'resendOtp']);

    Route::post('/login', [AuthController::class, 'login']);
   Route::post('/forget-password', [AuthController::class, 'forgetPassword']);

    Route::post('/reset-password', [AuthController::class, 'resetPassword']);
    Route::middleware('auth:sanctum')->post('/logout', [AuthController::class, 'logout']);
});