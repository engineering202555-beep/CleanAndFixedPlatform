<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ChangePasswordRequest;
use App\Http\Requests\Admin\LoginRequest;
use App\Http\Resources\Admin\AdminResource;
use App\Services\Auth\AuthServiceAdmin;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function __construct(
        private AuthServiceAdmin $authService
    ) {
    }

    public function login(LoginRequest $request)
    {
        $result = $this->authService->login($request->validated());

        return ApiResponse::success(
            [
                'admin' => new AdminResource($result['admin']),
                'token' => $result['token'],
            ],
            'Login successful.'
        );
    }

    public function changePassword(ChangePasswordRequest $request)
    {
        $admin = auth()->user();

        $this->authService->changePassword(
            $admin,
            $request->validated()
        );

        return ApiResponse::success(
            null,
            'Password changed successfully.'
        );
    }
    public function logout()
    {
        $this->authService->logout(auth()->user());

        return ApiResponse::success(
            null,
            'Logout successful.'
        );
    }

}
