<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\BlockProviderRequest;
use App\Models\ServiceProvider;
use App\Services\ServiceProvider\ServiceProvidersManageService;
use Illuminate\Http\Request;

class ServiceProvidersManageController extends Controller
{
    public function __construct(
        private readonly ServiceProvidersManageService $service
    ) {
    }

    public function deleteServiceProvider(ServiceProvider $serviceProvider)
    {
        $this->service->delete($serviceProvider);

        return ApiResponse::success(null, 'تم حذف مقدم الخدمة بنجاح.');
    }

    public function block(BlockProviderRequest $request, ServiceProvider $serviceProvider)
    {
        $this->service->block($serviceProvider, $request->validated());

        return ApiResponse::success(null, 'تم حظر مقدم الخدمة بنجاح.');
    }

    public function unblock(ServiceProvider $serviceProvider)
    {
        $this->service->unblock($serviceProvider);

        return ApiResponse::success(null, 'تم فك الحظر عن مقدم الخدمة بنجاح.');
    }

}
