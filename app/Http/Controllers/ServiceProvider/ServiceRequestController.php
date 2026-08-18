<?php

namespace App\Http\Controllers\ServiceProvider;

use App\Events\ProviderUpdatedRequestStatus;
use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\ServiceProvider\ServiceRequest\ListEligibleRequestsRequest;
use App\Http\Requests\ServiceProvider\ServiceRequest\ProviderMyRequestsIndexRequest;
use App\Http\Resources\ServiceProvider\ServiceRequestResource;
use App\Models\ServiceRequest;
use App\Services\ServiceRequest\ProviderRequestActionService;
use App\Services\ServiceRequest\ProviderRequestQueryService;
use Illuminate\Http\Request;

class ServiceRequestController extends Controller
{
    public function __construct(
        private readonly ProviderRequestQueryService $queryService,
        private readonly ProviderRequestActionService $actionService,
    ) {
    }

    public function index(ListEligibleRequestsRequest $request)
    {
        $provider = $request->user()->serviceProvider;
        $requests = $this->queryService->getEligibleRequests($provider, $request->validated());

        $paginated = ServiceRequestResource::collection($requests)->response()->getData(true);

        return ApiResponse::success($paginated, 'تم جلب الطلبات المتاحة بنجاح');
    }

    public function show(Request $request, ServiceRequest $serviceRequest)
    {
        $provider = $request->user()->serviceProvider;
        $details = $this->queryService->getRequestDetails($provider, $serviceRequest);

        return ApiResponse::success(ServiceRequestResource::make($details), 'تم جلب تفاصيل الطلب بنجاح');
    }

    /**
     * accepted/scheduled → in_progress، أو inspection_accepted →
     * inspection_in_progress (حسب حالة الطلب الحالية).
     */
    public function start(Request $request, ServiceRequest $serviceRequest)
    {
        $provider = $request->user()->serviceProvider;
        $updated = $this->actionService->start($provider, $serviceRequest);

        return ApiResponse::success(ServiceRequestResource::make($updated), 'تم بدء تنفيذ الطلب بنجاح');
    }

    /**
     * جديد: in_progress → awaiting_confirmation، أو
     * inspection_in_progress → fault_detected. خلص الشغل، لسا ما
     * تأكد الدفع (بمسار التصليح تحديداً).
     */
    public function finish(Request $request, ServiceRequest $serviceRequest)
    {
        $provider = $request->user()->serviceProvider;
        $updated = $this->actionService->finish($provider, $serviceRequest);

        return ApiResponse::success(ServiceRequestResource::make($updated), 'تم إنهاء الكشف وتسجيل استلام المبلغ');
    }

    /**
     * awaiting_confirmation → completed (بعد ما الزبون أكد من طرفه
     * إنه الشغل خلص، مقدم الخدمة يأكد استلام الفلوس نقداً).
     */
    public function complete(Request $request, ServiceRequest $serviceRequest)
    {
        $provider = $request->user()->serviceProvider;
        $updated = $this->actionService->complete($provider, $serviceRequest);

        return ApiResponse::success(ServiceRequestResource::make($updated), 'تم تأكيد استلام الدفع وإنهاء الطلب بنجاح');
    }
    public function cancel(Request $request, ServiceRequest $serviceRequest)
    {
        $provider = $request->user()->serviceProvider;
        $updated = $this->actionService->cancel($provider, $serviceRequest);

        return ApiResponse::success(ServiceRequestResource::make($updated), 'تم إلغاء الطلب بنجاح');
    }

    public function myRequests(ProviderMyRequestsIndexRequest $request)
    {
        $provider = $request->user()->serviceProvider;

        $requests = $this->queryService->getMyRequests($provider, $request->validated());

        $paginated = ServiceRequestResource::collection($requests)->response()->getData(true);

        return ApiResponse::success($paginated, 'تم جلب طلباتك بنجاح');
    }
}

