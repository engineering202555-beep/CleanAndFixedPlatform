<?php

namespace App\Providers;

use App\Support\Otp\FakeOtpGateway;
use App\Support\Otp\OtpGatewayInterface;
use App\Support\Otp\WhatsappOtpDriver;
use Illuminate\Support\ServiceProvider;

class OtpServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(OtpGatewayInterface::class, function () {
            return match (config('services.otp.driver', 'fake')) {
                'whatsapp' => new WhatsappOtpDriver(
                    config('services.ultramsg.api_url'),
                    config('services.ultramsg.token'),
                ),
                default => new FakeOtpGateway(),
            };
        });
    }
}
