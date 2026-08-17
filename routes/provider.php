<?php


use App\Http\Controllers\Admin\CityController;
use App\Http\Controllers\Admin\ServiceCategoryController;
use App\Http\Controllers\ServiceProvider\FcmTokenController;
use App\Http\Controllers\ServiceProvider\OfferController;
use App\Http\Controllers\ServiceProvider\ProviderPreferencesController;
use App\Http\Controllers\ServiceProvider\ServiceRequestController;
use App\Http\Controllers\ServiceProvider\AuthController;
use App\Http\Controllers\ServiceProvider\ProfileServiceProviderController;

use App\Http\Controllers\ServiceProvider\ProviderComplaintController;
use Illuminate\Support\Facades\Route;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/otp/verify', [AuthController::class, 'verifyOtp']);
Route::post('/otp/resend', [AuthController::class, 'resendOtp']);
Route::post('/login', [AuthController::class, 'login']);
Route::get('/service-categories', [ServiceCategoryController::class, 'index']);
Route::get('/service-areas', [CityController::class, 'index'])->name('cities.index');

Route::middleware(['auth:sanctum', 'role:provider','provider.active'])->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
<<<<<<< HEAD
=======
    Route::get('/service-categories', [ServiceCategoryController::class, 'index']);
    Route::get('/service-areas', [CityController::class, 'index'])->name('cities.index');
       
    
    
    Route::get('/profileProvider',[ProfileServiceProviderController::class, 'showProfileServiceProvider']);
    Route::patch('/updateProfile',[ProfileServiceProviderController::class, 'updateProviderProfile']);
       Route::post('updateImageProfile',[ProfileServiceProviderController::class, 'updateProfileImage']);
>>>>>>> 87c706574bdd68d9d1ec6f48148e63c593b09710

    Route::get('/requests', [ServiceRequestController::class, 'index']);
    Route::get('/requests/{serviceRequest}', [ServiceRequestController::class, 'show']);
    Route::post('/requests/offers/{serviceRequest}', [OfferController::class, 'store']);
    Route::patch('/requests/start/{serviceRequest}', [ServiceRequestController::class, 'start']);
    Route::patch('/requests/complete/{serviceRequest}', [ServiceRequestController::class, 'complete']);
    Route::patch('/requests/cancel/{serviceRequest}', [ServiceRequestController::class, 'cancel']);
    Route::patch('/requests/finish/{serviceRequest}', [ServiceRequestController::class, 'finish']);
    Route::post('/fcm-token', [FcmTokenController::class, 'store']);
    Route::patch('/preferences/do-not-disturb', [ProviderPreferencesController::class, 'updateDoNotDisturb']);
//  Route::get('/offers', [OfferController::class, 'index']);



       Route::post('/storeProviderComplaint',[ProviderComplaintController::class, 'storeProviderComplaint']);
       Route::get('/providerComplaints',[ProviderComplaintController::class, 'getProviderComplaints']);
       Route::get('complaintsAgainstProvider',[ProviderComplaintController::class, 'complaintsAgainstProvider']
);

});
