<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\MostRequestedCategoriesStatsRequest;
use App\Http\Resources\Admin\MostRequestedCategoryResource;
use App\Http\Resources\Admin\ProviderDistributionByCategoryResource;
use App\Services\Stats\ServiceCategoryStatsService;

class ServiceCategoryStatsController extends Controller
{
    public function __construct(
        private readonly ServiceCategoryStatsService $service
    ) {
    }

    public function mostRequested(MostRequestedCategoriesStatsRequest $request)
    {
        $stats = $this->service->getMostRequested($request->validated());

        return ApiResponse::success(
            MostRequestedCategoryResource::collection($stats),
            'تم جلب أكثر أنواع الخدمات طلباً بنجاح'
        );
    }

    public function providerDistribution()
    {
        $stats = $this->service->getProviderDistribution();

        return ApiResponse::success(
            ProviderDistributionByCategoryResource::collection($stats),
            'تم جلب توزيع مقدمي الخدمة بنجاح'
        );
    }
}
