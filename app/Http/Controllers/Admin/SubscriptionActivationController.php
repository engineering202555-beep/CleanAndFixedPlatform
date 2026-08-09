<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Resources\Admin\SubscriptionProviderResource;
use App\Models\SubscriptionProvider;
use App\Services\Subscription\SubscriptionActivationService;

class SubscriptionActivationController extends Controller
{
    public function __construct(
        private readonly SubscriptionActivationService $service
    ) {
    }

    /**
     * ما في FormRequest هون بالقصد — الإجراء بلا أي بيانات مُدخلة
     * تحتاج تحقق (subscription_provider_id جاي من الراوت نفسه عبر
     * Route Model Binding)، كل الشرط يتفحص جوّا الـ Service.
     */
    public function __invoke(SubscriptionProvider $subscriptionProvider)
    {
        $activated = $this->service->activate($subscriptionProvider);

        return ApiResponse::success(
            SubscriptionProviderResource::make($activated->load(['serviceProvider.user', 'subscription'])),
            'تم تفعيل الاشتراك بنجاح'
        );
    }
}
