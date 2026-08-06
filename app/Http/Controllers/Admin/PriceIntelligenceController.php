<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\PriceComparisonRequest;
use App\Http\Requests\Admin\PriceTrendRequest;
use App\Http\Resources\Admin\PriceComparisonResource;
use App\Http\Resources\Admin\PriceTrendResource;
use App\Services\Stats\PriceIntelligenceService;

class PriceIntelligenceController extends Controller
{
    public function __construct(
        private readonly PriceIntelligenceService $service
    ) {
    }

    public function compare(PriceComparisonRequest $request)
    {
        $result = $this->service->compare($request->validated());

        return ApiResponse::success(
            PriceComparisonResource::make($result),
            'تم جلب مقارنة الأسعار بنجاح'
        );
    }

    public function monthlyTrend(PriceTrendRequest $request)
    {
        $trend = $this->service->monthlyTrend($request->validated());

        return ApiResponse::success(
            PriceTrendResource::collection($trend),
            'تم جلب اتجاه الأسعار الشهري بنجاح'
        );
    }
}
