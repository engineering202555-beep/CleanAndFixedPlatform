<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider as BaseServiceProvider;
use App\Models\ServiceProvider;
use App\Models\ServiceRequest;
use Illuminate\Database\Eloquent\Relations\Relation;
use App\Contracts\Customer\OTPServiceInterface;
use App\Services\Customer\OTPService;

class AppServiceProvider extends BaseServiceProvider
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
        /*
        |----------------------------------------------------------------
        | morphMap
        |----------------------------------------------------------------
        | بدون هذا، عمود imageable_type رح يخزن الاسم الكامل للكلاس
        | (مثلاً App\Models\ServiceRequest)، وإذا نقلت الموديل لمجلد
        | تاني أو غيّرت الـ Namespace مستقبلاً، كل الصور القديمة
        | بتنكسر علاقتها فوراً. الـ morphMap بيخزن اسم مستعار ثابت
        | (alias) بدل الاسم الكامل، وبيضل شغال حتى لو نقلت الملف.
        */
        Relation::morphMap([
            'service_request'  => ServiceRequest::class,
            'service_provider' => ServiceProvider::class,
        ]);
    }
}
