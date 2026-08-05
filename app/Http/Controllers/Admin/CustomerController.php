<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\CustomerFilterRequest;
use App\Http\Resources\Admin\CustomerBlockResource;
use App\Http\Resources\Admin\CustomerInfoResource;
use App\Models\Customer;
use App\Services\Customer\CustomerQueryService;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

class CustomerController extends Controller
{
    public function __construct(
        private readonly CustomerQueryService $service
    ) {
    }

    public function getCustomersByFilter(CustomerFilterRequest $request)
    {
        $customers = $this->service->getVerifiedCustomers($request->validated());

        // ->response()->getData(true) بيحافظ على links/meta (فيها total)
        // كاملة، وبنفس الوقت بيرجع array عادي نقدر نمرره لـ ApiResponse
        // الموحّد تبعك. البديل (تمرير الـ Collection مباشرة بدون
        // ->response()) بيفقد كل معلومات الترقيم.
        $paginated = CustomerInfoResource::collection($customers)->response()->getData(true);

        return ApiResponse::success($paginated, 'تم جلب الزبائن الموثّقين بنجاح');
    }

    public function getCustomersBlocked()
    {
        $customers = $this->service->getBlockedCustomers();

        $paginated = CustomerBlockResource::collection($customers)->response()->getData(true);

        return ApiResponse::success($paginated, 'تم جلب الزبائن المحظورين بنجاح');
    }
}
