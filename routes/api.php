<?php
use App\Http\Controllers\AuthController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');
Route::post('registerCustumer',[AuthController::class,'register']);
Route::post('loginCustumer',[AuthController::class,'login']);
Route::middleware('auth:sanctum')->post('logoutCustumer',[AuthController::class,'logout']);