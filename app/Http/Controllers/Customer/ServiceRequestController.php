<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreServiceRequestRequest;
use App\Services\Customer\ServiceRequestService;

class ServiceRequestController extends Controller
{
    public function __construct(
        private ServiceRequestService $service
    ) {}

    public function store(StoreServiceRequestRequest $request)
{
    return response()->json(

        $this->ServiceRequestService->store(
            auth()->user(),
            $request->validated()
        ),

        201

    );
}
}