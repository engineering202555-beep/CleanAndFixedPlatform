<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Contracts\Customer\OTPServiceInterface;
use App\Services\Customer\OTPService;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(
        OTPServiceInterface::class,
        OTPService::class
    );
        
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
