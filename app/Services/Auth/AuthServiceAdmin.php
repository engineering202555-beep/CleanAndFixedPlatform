<?php

namespace App\Services\Auth;

use App\Helpers\ApiResponse;
use App\Models\Admin;
use Illuminate\Support\Facades\Hash;
use Symfony\Component\HttpKernel\Exception\HttpException;

class AuthServiceAdmin
{
    public function login(array $data)
    {
        $admin = Admin::where('user_name', $data['user_name'])->first();

        if (! $admin || ! Hash::check($data['password'], $admin->password)) {
            throw new HttpException(401, 'Invalid username or password.');
        }

        $token = $admin->createToken('admin-token')->plainTextToken;

        return [
            'admin' => $admin,
            'token' => $token,
        ];
    }


    public function changePassword(Admin $admin, array $data)
    {
        if (! Hash::check($data['current_password'], $admin->password)) {
            throw new HttpException(
                400,
                'Current password is incorrect.'
            );
        }

        $admin->update([
            'password' => Hash::make($data['new_password']),
            'must_change_password' => false,
        ]);
    }

    public function logout(Admin $admin): void
    {
        $admin->currentAccessToken()->delete();
    }
}
