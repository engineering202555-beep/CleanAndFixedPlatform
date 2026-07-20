<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Resources\Admin\ServiceProviderDetailsResource;
use App\Http\Resources\Admin\ServiceProviderListResource;
use App\Http\Resources\Admin\ServiceProviderPendingResource;
use App\Models\ServiceProvider;
use App\Services\ServiceProvider\ServiceProviderService;
use Illuminate\Http\Request;

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
}
