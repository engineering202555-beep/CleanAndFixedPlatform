<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Http\Requests\Customer\SaveFcmTokenRequest;
use App\Services\Notification\FcmTokenService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CustomerFcmTokenController extends Controller
{
    public function __construct(
        private readonly FcmTokenService $fcmTokenService
    ) {
    }

    public function store(
        SaveFcmTokenRequest $request
    ): JsonResponse {
        
        $user = $request->user();

        $token = $this->fcmTokenService->saveToken(
            $user,
            $request->validated('fcm_token')
        );

        return response()->json([
            'success' => true,
            'message' => 'FCM token saved successfully.',
            'data' => [
                'id' => $token->id,
            ],
        ], 201);
    }
}