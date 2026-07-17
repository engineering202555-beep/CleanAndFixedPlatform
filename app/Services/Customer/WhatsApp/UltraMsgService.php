<?php

namespace App\Services\Customer\WhatsApp;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class UltraMsgService
{
    public function send(string $phone, string $message): bool
    {
        $response = Http::asForm()->post(
            config('services.ultramsg.base_url')
            . '/'
            . config('services.ultramsg.instance_id')
            . '/messages/chat',
            [
                'token' => config('services.ultramsg.token'),
                'to'    => $phone,
                'body'  => $message,
            ]
        );

        if ($response->successful()) {
            Log::info('WhatsApp OTP sent', [
                'phone' => $phone,
                'response' => $response->json(),
            ]);

            return true;
        }

        Log::error('UltraMsg Error', [
            'phone' => $phone,
            'response' => $response->body(),
        ]);

        return false;
    }
}