<?php

namespace App\Support\Otp;

use Illuminate\Support\Facades\Log;

class WhatsappOtpDriver implements OtpGatewayInterface
{
    public function __construct(
        private readonly string $apiUrl,
        private readonly string $token,
    ) {
    }

    /**
     * نفس آلية sendWhatsAppMessage() المعتمدة عندكم بالضبط (cURL
     * خام، نفس الـ Options، نفس شكل الـ Headers) — بس ملفوفة بكلاس
     * الـ Driver عشان تنسجم مع بنية الـ Interface. لا env() هون —
     * القيم كلها جايّة عبر الـ Constructor من config() (شوفي
     * OtpServiceProvider).
     *
     * تنبيه أمني: $params (وفيها $this->token) ما بتنطبع بأي Log
     * إطلاقاً هون — بس $response (رد UltraMsg) و$err (خطأ cURL إن
     * وُجد) بالظبط متل الكود المعتمد، وهذول ما بيحتووا التوكن.
     */
    public function send(string $phone, string $code): bool
    {
        $message = "رمز التحقق الخاص بك هو: {$code}\nصالح لمدة 10 دقائق.";

        $params = [
            'token' => $this->token,
            'to'    => $phone,
            'body'  => $message,
        ];

        $curl = curl_init();

        curl_setopt_array($curl, [
            CURLOPT_URL            => $this->apiUrl.'/messages/chat',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING       => '',
            CURLOPT_MAXREDIRS      => 10,
            CURLOPT_TIMEOUT        => 30,
            CURLOPT_SSL_VERIFYHOST => 0,
            CURLOPT_SSL_VERIFYPEER => 0,
            CURLOPT_HTTP_VERSION   => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST  => 'POST',
            CURLOPT_POSTFIELDS     => http_build_query($params),
            CURLOPT_HTTPHEADER     => [
                'content-type: application/x-www-form-urlencoded',
            ],
        ]);

        $response = curl_exec($curl);
        $err = curl_error($curl);
        curl_close($curl);

        if ($err) {
            Log::error("UltraMsg Error: {$err}");

            return false;
        }

        Log::info("UltraMsg Response: {$response}");

        return true;
    }
}
