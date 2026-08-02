<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Http\Requests\Customer\StoreReviewRequest;
use App\Services\Review\ReviewService;

class ReviewController extends Controller
{
    public function __construct(
        private ReviewService $reviewService
    ) {}

    public function Review(StoreReviewRequest $request)
    {
        return response()->json(

            $this->reviewService->Review(
                auth()->user(),
                $request->validated()
            ),

            201
        );
    }
}