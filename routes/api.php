<?php
//use App\Http\Controllers\AuthController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\VerifyOtpController;
Route::get('/user', function (Request $request) {
    return $request->user();

})->middleware('auth:sanctum');
