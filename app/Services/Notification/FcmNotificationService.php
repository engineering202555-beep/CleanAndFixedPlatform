<?php

namespace App\Services\Notification;

use App\Models\FcmToken;
use Illuminate\Support\Facades\Log;
use Kreait\Firebase\Contract\Messaging;
use Kreait\Firebase\Exception\Messaging\NotFound as FcmTokenNotFound;
use Kreait\Firebase\Exception\MessagingException;
use Kreait\Firebase\Messaging\CloudMessage;

class FcmNotificationService
{
    public function __construct(
        private readonly Messaging $messaging
    ) {
    }

    /**
     * Data Message خالص (بدون مفتاح "notification") — هذا "تحديث
     * آني" مش "إشعار" حسب توضيحك، فFlutter هو يلي بيقرر شو يعمل
     * فيه (تحديث قائمة بصمت، مش بالضرورة بانر إشعار).
     *
     * تُرسل لكل أجهزة المستخدم المسجّلة (البنية الحالية بجدول
     * fcm_tokens تسمح بأكتر من توكن لنفس user_id تلقائياً — كل
     * صف = جهاز، فالـ foreach هون أصلاً بيغطي "أكتر من جهاز").
     */
    public function sendToUser(int $userId, string $type, array $payload = []): void
    {
        $tokens = FcmToken::query()->where('user_id', $userId)->get(['id', 'fcm_token']);

        foreach ($tokens as $tokenRecord) {
            $this->sendToToken($tokenRecord->id, $tokenRecord->fcm_token, $type, $payload);
        }
    }

    private function sendToToken(int $tokenRecordId, string $token, string $type, array $payload): void
    {
        $message = CloudMessage::withTarget('token', $token)
            ->withData(array_merge(['type' => $type], $this->stringifyPayload($payload)));

        try {
            $this->messaging->send($message);
        } catch (FcmTokenNotFound $e) {
            // Firebase نفسها بتأكد إنه التوكن ملغى/غير صالح نهائياً
            // (مش خطأ شبكة مؤقت) — تنظيفه من جدولنا فوراً، وإلا
            // بيضل يفشل بصمت بكل إرسال مستقبلي لنفس المستخدم.
            FcmToken::whereKey($tokenRecordId)->delete();
        } catch (MessagingException $e) {
            // أي فشل تاني من Firebase (Rate limit، توكن غير صالح
            // الصيغة، مشكلة اتصال...) — يُسجَّل، ما يوقف باقي الأجهزة
            // (foreach بالخارج بيكمل لباقي التوكنات).
            Log::warning('FCM send failed', [
                'user_id' => $tokenRecordId,
                'type'    => $type,
                'message' => $e->getMessage(),
            ]);
        }
    }

    /**
     * FCM Data Payload بتقبل String values بس.
     */
    private function stringifyPayload(array $payload): array
    {
        return array_map(
            fn ($value) => is_scalar($value) ? (string) $value : json_encode($value),
            $payload
        );
    }
}

