<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ApproveProviderRequest;
use App\Http\Requests\Admin\ReconsiderProviderRequest;
use App\Models\ServiceProvider;
use App\Services\ServiceProvider\ServiceProviderRequestsService;
use Illuminate\Http\Request;

class ServiceProviderRequestsController extends Controller
{
    public function __construct(
        private readonly ServiceProviderRequestsService $service
    ) {
    }

    /**
     * اسم الراوت والـ URL يضلوا "approval" لأنهم بيوصفوا الإجراء
     * للعالم الخارجي، حتى لو تغيّر اسم الـ method الداخلي لـ decide().
     */
    public function approval(ApproveProviderRequest $request, ServiceProvider $serviceProvider)
    {
        $this->service->decide($serviceProvider, $request->validated());

        return ApiResponse::success(null, 'تم تحديث حالة مقدم الخدمة بنجاح.');
    }

    /**
     * إعادة النظر بطلب مرفوض سابقاً: قبول متأخر أو إبقاء الرفض
     * (مع إمكانية تحديث سبب الرفض).
     */
    public function reconsider(ReconsiderProviderRequest $request, ServiceProvider $serviceProvider)
    {
        $this->service->reconsider($serviceProvider, $request->validated());

        return ApiResponse::success(null, 'تم تحديث حالة الطلب بنجاح.');
    }
}
