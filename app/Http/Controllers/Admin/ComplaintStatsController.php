<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ComplaintStatsRequest;
use App\Http\Resources\Admin\ComplaintStatsResource;
use App\Services\Complaint\ComplaintStatsService;

class ComplaintStatsController extends Controller
{
    public function __construct(
        private readonly ComplaintStatsService $service
    ) {
    }

    public function __invoke(ComplaintStatsRequest $request)
    {
        $stats = $this->service->getStats($request->validated());

        return ApiResponse::success(ComplaintStatsResource::make($stats), 'تم جلب إحصائيات الشكاوى بنجاح');
    }
}
