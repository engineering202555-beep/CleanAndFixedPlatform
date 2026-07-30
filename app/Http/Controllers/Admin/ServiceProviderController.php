<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ApproveProviderRequest;
use App\Http\Requests\Admin\MostActiveProvidersRequest;
use App\Http\Requests\Admin\MostProviderComplainedRequest;
use App\Http\Requests\Admin\ServiceProviderFilterRequest;
use App\Http\Resources\Admin\MostActiveProvidersResource;
use App\Http\Resources\Admin\MostProviderComplainedResource;
use App\Http\Resources\Admin\ServiceProviderBlockedResource;
use App\Http\Resources\Admin\ServiceProviderDetailsResource;
use App\Http\Resources\Admin\ServiceProviderListResource;
use App\Http\Resources\Admin\ServiceProviderPendingResource;
use App\Http\Resources\Admin\ServiceProvidersRejectedResource;
use App\Models\ServiceProvider;
use App\Services\ServiceProvider\ServiceProviderService;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\JsonResponse;

class ServiceProviderController extends Controller
{
    public function __construct(
        private ServiceProviderService $service
    ) {
    }

    public function getApprovedProviders()
    {
        $providers = $this->service->getApprovedProviders();

        return ApiResponse::success(
            ServiceProviderListResource::collection($providers),
            'Service providers retrieved successfully.'
        );
    }

    public function getApprovedProvidersFilter(ServiceProviderFilterRequest $request)
    {
        $providers = $this->service->getApprovedProvidersFilter($request->validated());

        return ServiceProviderListResource::collection($providers)
            ->additional([
                'status'  => true,
                'message' => 'تم جلب مقدمي الخدمة المقبولين بنجاح',
            ])
            ->response();
    }

    public function getInfoProvider(ServiceProvider $serviceProvider)
    {
        $provider = $this->service
            ->getApprovedProviderDetails($serviceProvider);

        return ApiResponse::success(
            new ServiceProviderDetailsResource($provider),
            'Service provider retrieved successfully.'
        );
    }

    public function getPendingProviders()
    {
        $providers = $this->service->getPendingProviders();

        return ApiResponse::success(
            ServiceProviderPendingResource::collection($providers),
            'Pending service providers retrieved successfully.'
        );
    }

    public function getRejectedProviders()
    {
        $providers = $this->service->getRejectedProviders();

        return ApiResponse::success(
            ServiceProvidersRejectedResource::collection($providers),
            'تم جلب مقدمي الطلبات المرفوضين بنجاح.'
        );
    }

    public function getBlockedProviders(): JsonResponse
    {
        $providers = $this->service->getBlockedProviders();

        return ApiResponse::success(
            ServiceProviderBlockedResource::collection($providers),
            'تم جلب مقدمي الخدمة المحظورين بنجاح'
        );
    }

    public function mostActive(MostActiveProvidersRequest $request): JsonResponse
    {
        $providers = $this->service->getMostActiveThisMonth($request->integer('limit', 10));

        return ApiResponse::success(
            MostActiveProvidersResource::collection($providers),
            'تم جلب الأكثر نشاطاً هذا الشهر بنجاح'
        );
    }

    public function mostComplained(MostProviderComplainedRequest $request): JsonResponse
    {
        $providers = $this->service->getMostComplainedAgainst($request->integer('limit', 10));

        return ApiResponse::success(
            MostProviderComplainedResource::collection($providers),
            'تم جلب الأكثر شكاوى بنجاح'
        );
    }
}
