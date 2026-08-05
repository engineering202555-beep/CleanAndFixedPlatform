<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ServiceRequestGrowthStatsRequest;
use App\Http\Resources\Admin\MonthlyGrowthResource;
use App\Services\Stats\ServiceRequestGrowthStatsService;
use Illuminate\Http\Request;

class ServiceRequestGrowthStatsController extends Controller
{
    public function __construct(
        private readonly ServiceRequestGrowthStatsService $service
    ) {
    }

    public function __invoke(ServiceRequestGrowthStatsRequest $request)
    {
        $growth = $this->service->getMonthlyGrowth($request->validated());

        return ApiResponse::success(
            MonthlyGrowthResource::collection($growth),
            'تم جلب إحصائية نمو الطلبات بنجاح'
        );
    }
}
