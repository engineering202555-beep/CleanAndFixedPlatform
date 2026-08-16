<?php

namespace App\Support\Otp;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class UltraMsgOtpGateway implements OtpGatewayInterface
{
    public function __construct(
        private readonly string $baseUrl,
        private readonly string $instanceId,
        private readonly string $token,
    ) {
    }

    /**
     * فشل الإرسال (تعطّل UltraMsg، انتهاء صلاحية التوكن، رقم غير
     * صالح على واتساب...) لازم يُعامل كفشل فعلي، مش يُبتلع بصمت —
     * الـ Service يلي بينادي هاد بيقرر شو يصير بالـ OTP row لو رجعت false.
     */
    public function send(string $phone, string $code): bool
    {
        try {
            $response = Http::timeout(10)
                ->asForm()
                ->post("{$this->baseUrl}/{$this->instanceId}/messages/chat", [
                    'token' => $this->token,
                    'to'    => $phone,
                    'body'  => "رمز التحقق الخاص بك هو: {$code}\nصالح لمدة 10 دقائق.",
                ]);

            if (! $response->successful()) {
                Log::warning('UltraMsg OTP send failed', [
                    'phone'  => $phone,
                    'status' => $response->status(),
                    'body'   => $response->body(),
                ]);

                return false;
            }

            return true;
        } catch (\Throwable $e) {
            Log::error('UltraMsg OTP send exception', [
                'phone'   => $phone,
                'message' => $e->getMessage(),
            ]);

            return false;
        }
    }
}
