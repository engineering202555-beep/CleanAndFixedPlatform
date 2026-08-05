<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\CustomerBlockRequest;
use App\Http\Resources\Admin\CustomerBlockResource;
use App\Models\Customer;
use App\Services\Customer\CustomerManageService;
use Illuminate\Http\Request;

class CustomerManageController extends Controller
{
    public function __construct(
        private readonly CustomerManageService $service
    ) {
    }

    /**
     * ما في FormRequest هون بالقصد — الحذف ما بحمل أي بيانات مُدخلة
     * تحتاج تحقق (DELETE بلا Body)، الشرط الوحيد يتفحص جوّا الـ Service.
     */
    public function destroy(Customer $customer)
    {
        $this->service->delete($customer);

        return ApiResponse::success(null, 'تم حذف الزبون بنجاح.');
    }

    public function block(CustomerBlockRequest $request, Customer $customer)
    {
        $this->service->block($customer, $request->validated());

        return ApiResponse::success(null, 'تم حظر الزبون بنجاح.');
    }

    public function unblock(Customer $customer)
    {
        $this->service->unblock($customer);

        return ApiResponse::success(null, 'تم فك الحظر عن الزبون بنجاح.');
    }

}
