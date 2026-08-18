<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Customer\AuthController;
use App\Http\Controllers\Customer\OfferController;
use App\Http\Controllers\Customer\CategoryController;
use App\Http\Controllers\Customer\ReviewController;
use App\Http\Controllers\Customer\ServiceRequestController;
 use App\Http\Controllers\Customer\HomeController;
 use App\Http\Controllers\Customer\ProfileController;
  use App\Http\Controllers\Customer\ComplaintController;
  use App\Http\Controllers\Customer\FcmTokenController;
  use App\Http\Controllers\Customer\CustomerFcmTokenController;
Route::middleware(['auth:sanctum', 'role:customer'])->group(function () {

    Route::post('/store-service-requests', [ServiceRequestController::class, 'store']);

    Route::get('/service-requests/{serviceRequest}/offers', [OfferController::class, 'allOffer']);
 Route::get('/service-requests', [ServiceRequestController::class,'allRequest']);
Route::get('/service-requests/{serviceRequest}',[ServiceRequestController::class, 'showRequest']);
 Route::put('/customer/service-requests/{serviceRequest}',[ServiceRequestController::class, 'updateRequest']);
Route::patch('/customer/service-requests/{serviceRequest}/cancel',[ServiceRequestController::class, 'cancelRequest']);

Route::patch('/customer/service-requests/{serviceRequest}/confirm',[ServiceRequestController::class, 'confirmService']);

    Route::get('/offers/{offer}', [OfferController::class, 'showOffer']);
Route::post('/offers/{offer}/accept', [OfferController::class, 'acceptOffer']);
    Route::get('/categories', [CategoryController::class,'allCategory']);
  
    Route::get('/categories/search', [CategoryController::class,'searchCategory']);
      Route::get('/categories/{serviceCategory}', [CategoryController::class,'showCategory']);

      Route::post('/reviews', [ReviewController::class, 'review']);

 Route::get(
        '/customer/profile',
        [ProfileController::class, 'showProfileCustomer']
    );


    Route::put(
    '/customer/Updateprofile',
    [ProfileController::class, 'updateProfileCustomer']
);


Route::post('/customer/profile/image',[ProfileController::class, 'updateImageProfileCustomer']);
Route::get('/home_customer', [HomeController::class, 'HomeCustomer']);



   Route::post('/customer/complaints',[ComplaintController::class, 'storeComplaint'] );


      
   
   Route::post('/customer/fcm-token',[FcmTokenController::class, 'saveToken']);

 Route::post('/store_fcm_token',[CustomerFcmTokenController::class, 'store']);



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