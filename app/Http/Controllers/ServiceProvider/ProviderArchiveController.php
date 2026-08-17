<?php

namespace App\Http\Controllers\ServiceProvider;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Resources\ServiceProvider\ArchivedServiceRequestResource;
use App\Services\ServiceRequest\ProviderArchiveService;
use Illuminate\Http\Request;

class ProviderArchiveController extends Controller
{
    public function __construct(
        private readonly ProviderArchiveService $service
    ) {
    }

    public function __invoke(Request $request)
    {
        $provider = $request->user()->serviceProvider;

        $requests = $this->service->getCompletedRequests($provider, $request->integer('per_page', 15));

        $paginated = ArchivedServiceRequestResource::collection($requests)->response()->getData(true);

        return ApiResponse::success($paginated, 'تم جلب أرشيف الطلبات المكتملة بنجاح');
    }
}
