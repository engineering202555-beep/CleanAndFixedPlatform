<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\SubscriptionPlanIndexRequest;
use App\Http\Requests\Admin\StoreSubscriptionPlanRequest;
use App\Http\Requests\Admin\UpdateSubscriptionPlanRequest;
use App\Http\Resources\Admin\SubscriptionPlanResource;
use App\Models\Subscription;
use App\Services\Subscription\SubscriptionPlanManagementService;
use App\Services\Subscription\SubscriptionPlanQueryService;


class SubscriptionPlanController extends Controller
{
    public function __construct(
        private readonly SubscriptionPlanQueryService $queryService,
        private readonly SubscriptionPlanManagementService $managementService,
    ) {
    }

    public function index(SubscriptionPlanIndexRequest $request)
    {
        $plans = $this->queryService->getAll($request->validated());

        $paginated = SubscriptionPlanResource::collection($plans)->response()->getData(true);

        return ApiResponse::success($paginated, 'تم جلب خطط الاشتراك بنجاح');
    }

    public function show(Subscription $subscription)
    {
        $plan = $this->queryService->getById($subscription->id);

        return ApiResponse::success(SubscriptionPlanResource::make($plan), 'تم جلب تفاصيل الخطة بنجاح');
    }

    public function store(StoreSubscriptionPlanRequest $request)
    {
        $plan = $this->managementService->store($request->validated());

        return ApiResponse::success(
            SubscriptionPlanResource::make($plan->loadCount(['providerSubscriptions as total_subscribers_count'])),
            'تم إنشاء خطة الاشتراك بنجاح',
            201
        );
    }

    public function update(UpdateSubscriptionPlanRequest $request, Subscription $subscription)
    {
        $plan = $this->managementService->update($subscription, $request->validated());

        return ApiResponse::success(
            SubscriptionPlanResource::make($this->queryService->getById($plan->id)),
            'تم تعديل خطة الاشتراك بنجاح'
        );
    }

    public function destroy(Subscription $subscription)
    {
        $this->managementService->delete($subscription);

        return ApiResponse::success(null, 'تم حذف خطة الاشتراك بنجاح');
    }
}
