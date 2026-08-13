<?php

namespace App\Services\Notification;

use App\Models\Notification;
use App\Models\User;

class NotificationService
{
    public function __construct(
        private FirebaseNotificationService $firebase
    ) {}

    public function notify(
        User $user,
        string $type,
        string $title,
        string $body,
        ?int $referenceId = null,
        ?string $referenceType = null
    ): Notification {

        /*
        ==========================================
        1. حفظ الإشعار في Database
        ==========================================
        */

        $notification = Notification::create([
            'user_id' => $user->id,
            'reference_id' => $referenceId,
            'reference_type' => $referenceType,
            'type' => $type,
            'title' => $title,
            'body' => $body,
            'is_read' => false,
        ]);

        /*
        ==========================================
        2. تجهيز Data للـ Flutter
        ==========================================
        */

        $data = [
            'notification_id' => (string) $notification->id,
            'type' => $type,
            'reference_id' => $referenceId
                ? (string) $referenceId
                : '',
            'reference_type' => $referenceType ?? '',
        ];

        /*
        ==========================================
        3. إرسال Push عبر Firebase
        ==========================================
        */

        $this->firebase->sendToUser(
            $user,
            $title,
            $body,
            $data
        );

        return $notification;
    }
}