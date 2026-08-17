<?php

namespace App\Http\Controllers\ServiceProvider;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\ServiceProvider\ServiceRequest\SelectSubscriptionRequest;
use App\Http\Resources\ServiceProvider\CurrentSubscriptionResource;
use App\Http\Resources\ServiceProvider\SubscriptionPlanResource;
use App\Services\ServiceRequest\ProviderSubscriptionManagementService;
use App\Services\ServiceRequest\ProviderSubscriptionQueryService;
use Illuminate\Http\Request;

class SubscriptionController extends Controller
{
    // نفس مصفوفة الـ Eager Loading بكل مكان بيرجع CurrentSubscriptionResource
    private const SUBSCRIPTION_RELATIONS = ['subscription:id,type,requests_per_month'];

    public function __construct(
        private readonly ProviderSubscriptionQueryService $queryService,
        private readonly ProviderSubscriptionManagementService $managementService,
    ) {
    }

    public function plans()
    {
        return ApiResponse::success(
            SubscriptionPlanResource::collection($this->queryService->getAvailablePlans()),
            'تم جلب الخطط المتاحة بنجاح'
        );
    }

    public function current(Request $request)
    {
        $provider = $request->user()->serviceProvider;
        $subscription = $this->queryService->getCurrentSubscription($provider);

        return ApiResponse::success(
            $subscription ? CurrentSubscriptionResource::make($subscription->load(self::SUBSCRIPTION_RELATIONS)) : null,
            'تم جلب الاشتراك الحالي بنجاح'
        );
    }

    public function select(SelectSubscriptionRequest $request)
    {
        $provider = $request->user()->serviceProvider;
        $subscription = $this->managementService->selectPlan($provider, $request->validated('subscription_id'));

        return ApiResponse::success(
            CurrentSubscriptionResource::make($subscription->load(self::SUBSCRIPTION_RELATIONS)),
            'تم إرسال طلب الاشتراك بنجاح، بانتظار موافقة الإدارة بعد استلام الدفع',
            201
        );
    }

    public function cancel(Request $request)
    {
        $provider = $request->user()->serviceProvider;
        $subscription = $this->managementService->cancelSubscription($provider);

        return ApiResponse::success(
            CurrentSubscriptionResource::make($subscription->load(self::SUBSCRIPTION_RELATIONS)),
            'تم إلغاء الاشتراك بنجاح'
        );
    }
}
