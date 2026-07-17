<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Http\Requests\Customer\RegisterRequest;
use App\Http\Requests\Customer\LoginRequest;
use App\Http\Requests\Customer\ResendOtpRequest;
use App\Http\Requests\Customer\ForgetPasswordRequest;
use App\Http\Requests\Customer\ResetPasswordRequest;

use App\Http\Requests\Customer\VerifyOtpRequest;
use App\Services\Customer\AuthService;

use Illuminate\Http\Request;


class AuthController extends Controller
{
public function __construct(
        private AuthService $authService
    ) {}

    public function register(RegisterRequest $request)
    {
        return response()->json(
            $this->authService->register($request->validated()),
            201
        );
    }

    public function verifyOtp(VerifyOtpRequest $request)
    {
        return response()->json(
            $this->authService->verifyOtp($request->validated())
        );
    }

    public function resendOtp(ResendOtpRequest $request)
    {
        return response()->json(
            $this->authService->resendOtp(
                $request->phone_number
            )
        );
    }

    public function login(LoginRequest $request)
    {
        return response()->json(
            $this->authService->login($request->validated())
        );
    }

    public function logout(Request $request)
    {
        $this->authService->logout($request->user());

        return response()->json([
            'message' => 'Logged out successfully.'
        ]);
    }

public function forgetPassword(ForgetPasswordRequest $request)
{
    return response()->json(
        $this->authService->forgetPassword($request->validated())
    );
}

public function resetPassword(ResetPasswordRequest $request)
{
    return response()->json(
        $this->authService->resetPassword($request->validated())
    );
}




}


