<?php

namespace App\Services\Notification;

use App\Models\FcmToken;
use App\Models\User;

class FcmTokenService
{
    public function saveToken(User $user, string $token): FcmToken
    {
        return FcmToken::updateOrCreate(
            [
                'user_id' => $user->id,
                'fcm_token' => $token,
            ],
            [
                'user_id' => $user->id,
                'fcm_token' => $token,
            ]
        );
    }

    public function deleteToken(User $user, string $token): void
    {
        FcmToken::where('user_id', $user->id)
            ->where('fcm_token', $token)
            ->delete();
    }
}