<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ReviewIndexRequest;
use App\Http\Resources\Admin\ReviewResource;
use App\Services\Review\ReviewQueryService;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    public function __construct(
        private readonly ReviewQueryService $service
    ) {
    }

    public function index(ReviewIndexRequest $request)
    {
        $reviews = $this->service->getAll($request->validated());

        $paginated = ReviewResource::collection($reviews)->response()->getData(true);

        return ApiResponse::success($paginated, 'تم جلب التقييمات بنجاح');
    }
}
