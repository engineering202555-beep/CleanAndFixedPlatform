<?php

namespace App\Services\CustomerAuth\WhatsApp;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class UltraMsgService
{
    public function send(string $phone, string $message): bool
    {
        $response = Http::asForm()->post(
            config('services.ultramsg.api_url')
            . '/'
            . config('services.ultramsg.instance_id')
            . '/messages/chat',
            [
                'token' => config('services.ultramsg.token'),
                'to'    => $phone,
                'body'  => $message,
            ]
        );
Log::info('Sending to WhatsApp', [
    'phone' => $phone,
]);
        if ($response->successful()) {
            Log::info('WhatsApp Otp sent', [
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
