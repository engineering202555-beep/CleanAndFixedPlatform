<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ComplaintIndexRequest;
use App\Http\Resources\Admin\ComplaintDetailResource;
use App\Http\Resources\Admin\ComplaintResource;
use App\Models\Complaint;
use App\Services\Complaint\ComplaintDetailService;
use App\Services\Complaint\ComplaintQueryService;

class ComplaintController extends Controller
{
    public function __construct(
        private readonly ComplaintQueryService $queryService,
        private readonly ComplaintDetailService $detailService,
    ) {
    }

    public function index(ComplaintIndexRequest $request)
    {
        $complaints = $this->queryService->getAll($request->validated());

        $paginated = ComplaintResource::collection($complaints)->response()->getData(true);

        return ApiResponse::success($paginated, 'تم جلب الشكاوى بنجاح');
    }

    public function show(Complaint $complaint)
    {
        $details = $this->detailService->getDetails($complaint);

        return ApiResponse::success(ComplaintDetailResource::make($details), 'تم جلب تفاصيل الشكوى بنجاح');
    }
}
