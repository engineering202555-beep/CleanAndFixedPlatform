<?php

namespace App\Support\Otp;

interface OtpGatewayInterface
{
    /**
     * @return bool نجاح الإرسال فعلياً (مش بس نجاح استدعاء الـ API)
     */
    public function send(string $phone, string $code): bool;
}
