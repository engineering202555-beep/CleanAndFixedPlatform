<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\GrantComplimentaryMonthRequest;
use App\Http\Requests\Admin\SubscriptionBreakdownRequest;
use App\Http\Resources\Admin\ProviderSubscriptionBreakdownResource;
use App\Models\ServiceProvider;
use App\Services\ServiceProvider\ServiceProviderSubscriptionsDetailsService;
use App\Services\Subscription\SubscriptionService;

class ServiceProviderSubscriptionsController extends Controller
{
    public function __construct(
        private readonly ServiceProviderSubscriptionsDetailsService $detailsService,
        private readonly SubscriptionService $subscriptionService,
    ) {
    }

    public function getProvidersSubscriptionsDetails(SubscriptionBreakdownRequest $request)
    {
        $providers = $this->detailsService->getSubscriptionBreakdown($request->integer('per_page', 15));

        return ApiResponse::success(
            ProviderSubscriptionBreakdownResource::collection($providers),
            'تم جلب توزيع الاشتراكات بنجاح'
        );
    }

    public function grantComplimentarySubscription(GrantComplimentaryMonthRequest $request, ServiceProvider $serviceProvider)
    {
        $this->subscriptionService->grantComplimentaryMonth($serviceProvider, $request->validated());

        return ApiResponse::success(null, 'تم منح مقدم الخدمة شهراً مجانياً من الخطة المدفوعة بنجاح.');
    }
}
