<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\SubscriptionRevenueStatsRequest;
use App\Http\Resources\Admin\SubscriptionRevenueStatsResource;
use App\Services\Stats\SubscriptionRevenueStatsService;
use Illuminate\Http\Request;

class SubscriptionRevenueStatsController extends Controller
{
    public function __construct(
        private readonly SubscriptionRevenueStatsService $service
    ) {
    }

    public function __invoke(SubscriptionRevenueStatsRequest $request)
    {
        $stats = $this->service->getStats($request->validated());

        return ApiResponse::success(
            SubscriptionRevenueStatsResource::make($stats),
            'تم جلب إحصائية إيرادات الاشتراكات بنجاح'
        );
    }
}
