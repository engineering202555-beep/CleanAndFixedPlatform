<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreCityWithAreasRequest;
use App\Http\Requests\Admin\UpdateCityNameRequest;
use App\Http\Resources\Admin\CityWithAreasResource;
use App\Services\Location\ServiceAreaManagementService;
use App\Services\Location\ServiceAreaQueryService;
use Illuminate\Http\Request;

class CityController extends Controller
{
    public function __construct(
        private readonly ServiceAreaQueryService $queryService,
        private readonly ServiceAreaManagementService $managementService,
    ) {
    }

    public function index()
    {
        $cities = $this->queryService->getCitiesWithAreas();

        $paginated = CityWithAreasResource::collection($cities)->response()->getData(true);

        return ApiResponse::success($paginated, 'تم جلب المدن والمناطق بنجاح');
    }

    /**
     * قائمة القيم الفريدة بس، عشان الفرونت إند يبني منها الـ Dropdown.
     */
    public function citiesDropdown()
    {
        return ApiResponse::success(
            $this->queryService->getDistinctCities(),
            'تم جلب قائمة المدن بنجاح'
        );
    }

    public function store(StoreCityWithAreasRequest $request)
    {
        $areas = $this->managementService->createCityWithAreas($request->validated());

        return ApiResponse::success(
            CityWithAreasResource::make(['city' => $request->input('city'), 'areas' => $areas]),
            'تم إنشاء المدينة والمناطق بنجاح',
            201
        );
    }

    public function renameCity(UpdateCityNameRequest $request, string $city)
    {
        $this->managementService->renameCity($city, $request->validated()['new_name']);

        return ApiResponse::success(null, 'تم تعديل اسم المدينة بنجاح');
    }

    public function destroyCity(string $city)
    {
        $this->managementService->deleteCity($city);

        return ApiResponse::success(null, 'تم حذف المدينة وكل مناطقها بنجاح');
    }
}
