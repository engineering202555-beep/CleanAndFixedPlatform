<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\BlockedProviderIndexRequest;
use App\Http\Resources\Admin\BlockedProviderResource;
use App\Services\Customer\BlockedProviderQueryService;
use Illuminate\Http\Request;

class BlockedProviderController extends Controller
{
    public function __construct(
        private readonly BlockedProviderQueryService $service
    ) {
    }

    public function index(BlockedProviderIndexRequest $request)
    {
        $blocks = $this->service->getAll($request->validated());

        $paginated = BlockedProviderResource::collection($blocks)->response()->getData(true);

        return ApiResponse::success($paginated, 'تم جلب قائمة الحظر بنجاح');
    }
}
