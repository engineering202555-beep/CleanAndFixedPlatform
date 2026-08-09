<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateComplaintStatusRequest;
use App\Http\Resources\Admin\ComplaintResource;
use App\Models\Complaint;
use App\Services\Complaint\ComplaintStatusService;

class ComplaintStatusController extends Controller
{
    public function __construct(
        private readonly ComplaintStatusService $service
    ) {
    }

    public function __invoke(UpdateComplaintStatusRequest $request, Complaint $complaint)
    {
        $updated = $this->service->transition($complaint, $request->validated());

        return ApiResponse::success(
            ComplaintResource::make($updated->load(['user', 'againstUser'])),
            'تم تحديث حالة الشكوى بنجاح'
        );
    }
}
