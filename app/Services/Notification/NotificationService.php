<?php

namespace App\Services\Notification;

use App\Models\Notification;
use App\Models\User;

class NotificationService
{
    public function __construct(
        private readonly FcmNotificationService $fcm
    ) {
    }

    public function notify(
        User $user,
        string $type,
        string $title,
        string $body,
        ?int $referenceId = null,
        ?string $referenceType = null
    ): Notification {

        /*
        ============================================
        1. حفظ الإشعار في قاعدة البيانات
        ============================================
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
        ============================================
        2. تجهيز Data للـ Flutter
        ============================================
        */

        $payload = [

            'notification_id' =>
                (string) $notification->id,

            'type' =>
                $type,

            'reference_id' =>
                $referenceId !== null
                    ? (string) $referenceId
                    : '',

            'reference_type' =>
                $referenceType ?? '',
        ];


        /*
        ============================================
        3. إرسال Push Notification
        ============================================
        */

        $this->fcm->sendToUser(
            $user->id,
            $type,
            array_merge(
                $payload,
                [
                    'title' => $title,
                    'body' => $body,
                ]
            )
        );


        /*
        ============================================
        4. إرجاع Notification
        ============================================
        */

        return $notification;
    }
}