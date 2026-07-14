<?php


use App\Http\Controllers\Admin\AuthController;
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
    });


