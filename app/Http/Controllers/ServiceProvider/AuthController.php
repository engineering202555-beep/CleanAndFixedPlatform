<?php

namespace App\Http\Controllers\ServiceProvider;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\ServiceProvider\Auth\LoginProviderRequest;
use App\Http\Requests\ServiceProvider\Auth\RegisterProviderRequest;
use App\Http\Requests\ServiceProvider\Auth\ResendOtpRequest;
use App\Http\Requests\ServiceProvider\Auth\VerifyOtpRequest;
use App\Http\Resources\ServiceProvider\ProviderProfileResource;
use App\Models\User;
use App\Services\Auth\OtpService;
use App\Services\Auth\ProviderAuthService;
use App\Services\Auth\ProviderRegistrationService;

class AuthController extends Controller
{
    public function __construct(
        private readonly ProviderRegistrationService $registrationService,
        private readonly ProviderAuthService $authService,
        private readonly OtpService $otpService,
    ) {
    }

    public function register(RegisterProviderRequest $request)
    {
        $user = $this->registrationService->register($request->validated());

        return ApiResponse::success(
            ProviderProfileResource::make($user->load('serviceProvider')),
            'تم التسجيل بنجاح، تم إرسال رمز التحقق إلى رقم هاتفك عبر واتساب.',
            201
        );
    }

    public function verifyOtp(VerifyOtpRequest $request)
    {
        $user = User::where('phone_number', $request->validated('phone_number'))->firstOrFail();

        $this->otpService->verify($user, $request->validated('code'));

        return ApiResponse::success(
            ProviderProfileResource::make($user->load('serviceProvider')),
            'تم توثيق رقم الهاتف بنجاح. حسابك الآن بانتظار موافقة الإدارة.'
        );
    }

    public function resendOtp(ResendOtpRequest $request)
    {
        $user = User::where('phone_number', $request->validated('phone_number'))->firstOrFail();

        $this->otpService->send($user);

        return ApiResponse::success(null, 'تم إرسال رمز تحقق جديد.');
    }

    public function login(LoginProviderRequest $request)
    {
        $result = $this->authService->login($request->validated());

        return ApiResponse::success([
            'provider' => ProviderProfileResource::make($result['user']->load('serviceProvider')),
            'token'    => $result['token'],
        ], 'تم تسجيل الدخول بنجاح');
    }

    public function logout(\Illuminate\Http\Request $request)
    {
        $this->authService->logout($request->user());

        return ApiResponse::success(null, 'تم تسجيل الخروج بنجاح');
    }
}
