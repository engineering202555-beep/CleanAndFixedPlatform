<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ServiceProviderController extends Controller
{
    public function __construct(
        private ServiceProviderService $service
    ) {
    }

    public function  getApprovedProviders()
    {
        $providers = $this->service->index();

        return ApiResponse::success(
            ServiceProviderResource::collection($providers),
            'Service providers retrieved successfully.'
        );
    }
}
