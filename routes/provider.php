<?php


use App\Http\Controllers\Admin\CityController;
use App\Http\Controllers\Admin\ServiceCategoryController;
use App\Http\Controllers\ServiceProvider\AuthController;
use Illuminate\Support\Facades\Route;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/otp/verify', [AuthController::class, 'verifyOtp']);
Route::post('/otp/resend', [AuthController::class, 'resendOtp']);
Route::post('/login', [AuthController::class, 'login']);

Route::middleware(['auth:sanctum', 'role:provider','provider.active'])->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/service-categories', [ServiceCategoryController::class, 'index']);
    Route::get('/service-areas', [CityController::class, 'index'])->name('cities.index');


});
