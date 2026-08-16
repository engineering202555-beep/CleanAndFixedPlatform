<?php

namespace App\Support\Otp;

use Illuminate\Support\Facades\Log;

class FakeOtpGateway implements OtpGatewayInterface
{
    public function send(string $phone, string $code): bool
    {
        Log::info("[FAKE OTP] {$phone} => {$code}");

        return true;
    }
}
