<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AddAreasToCityRequest;
use App\Http\Requests\Admin\UpdateAreaNameRequest;
use App\Http\Resources\Admin\AreaResource;
use App\Models\ServiceArea;
use App\Services\Location\ServiceAreaManagementService;
use Illuminate\Http\Request;

class AreaController extends Controller
{
    public function __construct(
        private readonly ServiceAreaManagementService $service
    ) {
    }

    public function store(AddAreasToCityRequest $request)
    {
        $areas = $this->service->addAreasToCity($request->validated());

        return ApiResponse::success(
            AreaResource::collection($areas),
            'تم إضافة المناطق بنجاح',
            201
        );
    }

    public function update(UpdateAreaNameRequest $request, ServiceArea $serviceArea)
    {
        $area = $this->service->renameArea($serviceArea, $request->validated()['area_name']);

        return ApiResponse::success(AreaResource::make($area), 'تم تعديل اسم المنطقة بنجاح');
    }

    public function destroy(ServiceArea $serviceArea)
    {
        $this->service->deleteArea($serviceArea);

        return ApiResponse::success(null, 'تم حذف المنطقة بنجاح');
    }
}
