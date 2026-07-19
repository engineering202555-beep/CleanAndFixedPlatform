<?php


use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\ServiceProviderController;
use Illuminate\Support\Facades\Route;


    Route::post('/login', [AuthController::class, 'login']);

    Route::middleware([
        'auth:sanctum',
        'role:admin',
    ])->group(function () {

        Route::post('/change-password', [AuthController::class, 'changePassword']);

    });

    Route::middleware([
        'auth:sanctum',
        'role:admin',
        'force.password.change',
    ])->group(function () {

        Route::post('/logout', [AuthController::class, 'logout']);
        Route::get('/service-providers', [ServiceProviderController::class, ' getApprovedProviders']);

    });


