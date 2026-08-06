<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AreaDensityStatsRequest;
use App\Http\Requests\Admin\ComplaintsByAreaStatsRequest;
use App\Http\Requests\Admin\GeographicGrowthStatsRequest;
use App\Http\Requests\Admin\HotAreasStatsRequest;
use App\Http\Requests\Admin\ProviderDistributionStatsRequest;
use App\Http\Requests\Admin\SupplyDemandStatsRequest;
use App\Http\Resources\Admin\ComplaintsByAreaResource;
use App\Http\Resources\Admin\DensityPointResource;
use App\Http\Resources\Admin\GeographicGrowthResource;
use App\Http\Resources\Admin\HotAreaResource;
use App\Http\Resources\Admin\ProviderDistributionResource;
use App\Http\Resources\Admin\SupplyDemandResource;
use App\Services\Stats\ComplaintsByAreaStatsService;
use App\Services\Stats\GeographicGrowthStatsService;
use App\Services\Stats\HotAreaStatsService;
use App\Services\Stats\ProviderDistributionStatsService;
use App\Services\Stats\SupplyDemandStatsService;

class AreaStatsController extends Controller
{
    public function __construct(
        private readonly HotAreaStatsService $hotAreaService,
        private readonly ComplaintsByAreaStatsService $complaintsService,
        private readonly ProviderDistributionStatsService $distributionService,
        private readonly SupplyDemandStatsService $supplyDemandService,
        private readonly GeographicGrowthStatsService $growthService,
    ) {
    }

    public function hotAreas(HotAreasStatsRequest $request)
    {
        $areas = $this->hotAreaService->getHotAreas($request->validated());

        return ApiResponse::success(HotAreaResource::collection($areas), 'تم جلب أكثر المناطق طلباً بنجاح');
    }

    public function density(AreaDensityStatsRequest $request)
    {
        $points = $this->hotAreaService->getDensityMap($request->validated());

        return ApiResponse::success(DensityPointResource::collection($points), 'تم جلب بيانات الكثافة بنجاح');
    }

    public function complaints(ComplaintsByAreaStatsRequest $request)
    {
        $stats = $this->complaintsService->getStats($request->validated());

        return ApiResponse::success(ComplaintsByAreaResource::collection($stats), 'تم جلب إحصائية الشكاوى بنجاح');
    }

    public function providerDistribution(ProviderDistributionStatsRequest $request)
    {
        $areas = $this->distributionService->getDistribution($request->integer('per_page', 15));

        $paginated = ProviderDistributionResource::collection($areas)->response()->getData(true);

        return ApiResponse::success($paginated, 'تم جلب توزيع مقدمي الخدمة بنجاح');
    }

    public function supplyDemand(SupplyDemandStatsRequest $request)
    {
        $rows = $this->supplyDemandService->getComparison($request->validated());

        return ApiResponse::success(SupplyDemandResource::collection($rows), 'تم جلب مقارنة العرض والطلب بنجاح');
    }

    public function geographicGrowth(GeographicGrowthStatsRequest $request)
    {
        $rows = $this->growthService->getMonthlyGrowth($request->validated());

        return ApiResponse::success(GeographicGrowthResource::collection($rows), 'تم جلب إحصائية النمو الجغرافي بنجاح');
    }
}
