<?php

namespace App\Services\Notification;

use App\Models\FcmToken;
use Illuminate\Support\Facades\Log;
use Kreait\Firebase\Contract\Messaging;
use Kreait\Firebase\Exception\Messaging\NotFound as FcmTokenNotFound;
use Kreait\Firebase\Exception\MessagingException;
use Kreait\Firebase\Messaging\AndroidConfig;
use Kreait\Firebase\Messaging\ApnsConfig;
use Kreait\Firebase\Messaging\CloudMessage;

class FcmNotificationService
{
    public function __construct(
        private readonly Messaging $messaging
    ) {
    }

    /**
     * إرسال Data Message إلى جميع أجهزة المستخدم.
     *
     * $highPriority: للطلبات المستعجلة فقط.
     * تضبط أولوية التوصيل على مستوى نظام التشغيل
     * Android/iOS.
     */
    public function sendToUser(
        int $userId,
        string $type,
        array $payload = [],
        bool $highPriority = false
    ): void {
        $tokens = FcmToken::query()
            ->where('user_id', $userId)
            ->get(['id', 'fcm_token']);

        foreach ($tokens as $tokenRecord) {
            $this->sendToToken(
                $tokenRecord->id,
                $tokenRecord->fcm_token,
                $type,
                $payload,
                $highPriority
            );
        }
    }

    /**
     * إرسال الرسالة إلى جهاز واحد.
     */
    private function sendToToken(
        int $tokenRecordId,
        string $token,
        string $type,
        array $payload,
        bool $highPriority = false
    ): void {
        $message = CloudMessage::withTarget(
            'token',
            $token
        )->withData(
            array_merge(
                [
                    'type' => $type,
                ],
                $this->stringifyPayload($payload)
            )
        );

        if ($highPriority) {
            $message = $message
                ->withAndroidConfig(AndroidConfig::fromArray([
                    'priority' => 'high',
                ]))
                ->withApnsConfig(ApnsConfig::fromArray([
                    'headers' => [
                        'apns-priority' => '10',
                    ],
                ]));
        }

        try {
            $this->messaging->send($message);
        } catch (FcmTokenNotFound $e) {
            /*
             * Firebase أخبرتنا أن الـ token لم يعد صالحاً.
             * نحذفه من قاعدة البيانات.
             */
            FcmToken::whereKey($tokenRecordId)->delete();

            Log::info('Invalid FCM token deleted', [
                'token_record_id' => $tokenRecordId,
            ]);
        } catch (MessagingException $e) {
            /*
             * أي خطأ آخر من Firebase.
             * لا نوقف إرسال الإشعارات لباقي الأجهزة.
             */
            Log::warning('FCM send failed', [
                'token_record_id' => $tokenRecordId,
                'type' => $type,
                'message' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Firebase Data Message تحتاج قيم String.
     */
    private function stringifyPayload(array $payload): array
    {
        return array_map(
            function ($value) {
                if (is_scalar($value)) {
                    return (string) $value;
                }

                return json_encode(
                    $value,
                    JSON_UNESCAPED_UNICODE
                );
            },
            $payload
        );
    }
}
