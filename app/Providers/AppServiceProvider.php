<?php

namespace App\Providers;

use App\Models\Offer;
use App\Observers\OfferObserver;
use App\Observers\ServiceRequestObserver;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider as BaseServiceProvider;
use App\Models\ServiceProvider;
use App\Models\ServiceRequest;
use Illuminate\Database\Eloquent\Relations\Relation;
use App\Contracts\Customer\OTPServiceInterface;
use App\Services\CustomerAuth\OTPService;

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

        ServiceRequest::observe(ServiceRequestObserver::class);
        Offer::observe(OfferObserver::class);

       Event::listen(
            NewServiceRequestEligible::class,
            SendNewServiceRequestFcm::class,
        );

        Event::listen(
            OfferAccepted::class,
            HandleOfferAccepted::class,
        );

        Event::listen(
            OfferSuperseded::class,
            SendOfferSupersededFcm::class,
        );

        Event::listen(
            ProviderUpdatedRequestStatus::class,
            NotifyCustomerOfRequestUpdate::class,
        );
    }
}
