<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\CustomerGrowthStatsRequest;
use App\Http\Resources\Admin\MonthlyGrowthResource;
use App\Services\Stats\CustomerGrowthStatsService;
use Illuminate\Http\Request;

class CustomerGrowthStatsController extends Controller
{
    public function __construct(
        private readonly CustomerGrowthStatsService $service
    ) {
    }

    public function __invoke(CustomerGrowthStatsRequest $request)
    {
        $growth = $this->service->getMonthlyGrowth($request->validated());

        return ApiResponse::success(
            MonthlyGrowthResource::collection($growth),
            'تم جلب إحصائية نمو الزبائن بنجاح'
        );
    }
}
