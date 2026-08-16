<?php

namespace App\Services\Notification;

use App\Models\User;
use Kreait\Firebase\Factory;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification;

class FirebaseNotificationService
{
    protected $messaging;

    public function __construct()
    {
        $firebase = (new Factory)
            ->withServiceAccount(
                config('services.firebase.credentials')
            );

        $this->messaging = $firebase->createMessaging();
    }

    public function sendToToken(
        string $token,
        string $title,
        string $body,
        array $data = []
    ) {
        $notification = Notification::create(
            $title,
            $body
        );

        $message = CloudMessage::withTarget(
            'token',
            $token
        )
            ->withNotification($notification)
            ->withData($data);

        return $this->messaging->send($message);
    }

    public function sendToUser(
        User $user,
        string $title,
        string $body,
        array $data = []
    ): void {

        $tokens = $user->fcmTokens()
            ->pluck('fcm_token')
            ->toArray();

        foreach ($tokens as $token) {
            $this->sendToToken(
                $token,
                $title,
                $body,
                $data
            );
        }
    }
}