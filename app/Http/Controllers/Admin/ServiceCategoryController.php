<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ServiceCategoryIndexRequest;
use App\Http\Requests\Admin\StoreServiceCategoryRequest;
use App\Http\Requests\Admin\UpdateServiceCategoryRequest;
use App\Http\Resources\Admin\ServiceCategoryResource;
use App\Models\ServiceCategory;
use App\Services\ServiceCategory\ServiceCategoryManagementService;
use App\Services\ServiceCategory\ServiceCategoryQueryService;

class ServiceCategoryController extends Controller
{
    public function __construct(
        private readonly ServiceCategoryQueryService $queryService,
        private readonly ServiceCategoryManagementService $managementService,
    ) {
    }

    public function index(ServiceCategoryIndexRequest $request)
    {
        $categories = $this->queryService->getAll($request->validated());

        $paginated = ServiceCategoryResource::collection($categories)->response()->getData(true);

        return ApiResponse::success($paginated, 'تم جلب أنواع الخدمات بنجاح');
    }

    public function store(StoreServiceCategoryRequest $request)
    {

        $category = $this->managementService->store($request->validated());

        return ApiResponse::success(
            ServiceCategoryResource::make($category->loadCount(['serviceProviders', 'serviceRequests'])),
            'تم إنشاء نوع الخدمة بنجاح',
            201
        );
    }

    public function update(UpdateServiceCategoryRequest $request, ServiceCategory $serviceCategory)
    {
        $category = $this->managementService->update($serviceCategory, $request->validated());

        return ApiResponse::success(
            ServiceCategoryResource::make($category->loadCount(['serviceProviders', 'serviceRequests'])),
            'تم تعديل نوع الخدمة بنجاح'
        );
    }

    public function destroy(ServiceCategory $serviceCategory)
    {
        $this->managementService->delete($serviceCategory);

        return ApiResponse::success(null, 'تم حذف نوع الخدمة بنجاح');
    }
}
