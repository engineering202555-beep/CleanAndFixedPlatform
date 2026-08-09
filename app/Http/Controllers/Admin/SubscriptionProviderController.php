<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\SubscriptionProviderIndexRequest;
use App\Http\Resources\Admin\SubscriptionProviderResource;
use App\Models\SubscriptionProvider;
use App\Services\Subscription\SubscriptionProviderQueryService;

class SubscriptionProviderController extends Controller
{
    public function __construct(
        private readonly SubscriptionProviderQueryService $service
    ) {
    }

    public function index(SubscriptionProviderIndexRequest $request)
    {
        $subscriptions = $this->service->getAll($request->validated());

        $paginated = SubscriptionProviderResource::collection($subscriptions)->response()->getData(true);

        return ApiResponse::success($paginated, 'تم جلب اشتراكات مقدمي الخدمة بنجاح');
    }

    public function show(SubscriptionProvider $subscriptionProvider)
    {
        $subscription = $this->service->getById($subscriptionProvider->id);

        return ApiResponse::success(
            SubscriptionProviderResource::make($subscription),
            'تم جلب تفاصيل الاشتراك بنجاح'
        );
    }
}
