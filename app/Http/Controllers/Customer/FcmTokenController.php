<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\Customer\SaveFcmTokenRequest;
use App\Services\CustomerAuth\FcmTokenService;
class FcmTokenController extends Controller
{
    public function __construct(
        private FcmTokenService $fcmTokenService
    ) {}
   public function saveToken(SaveFcmTokenRequest $request) {
    $token = $this->fcmTokenService->saveToken(
        $request->user(),
        $request->validated('fcm_token')
    );

    return response()->json([
        'message' => 'FCM token saved successfully.',
    ]);
}




}
