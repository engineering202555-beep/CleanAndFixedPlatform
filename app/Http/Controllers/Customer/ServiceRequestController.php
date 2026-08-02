<?php

namespace App\Http\Controllers\Customer;
use App\Models\ServiceRequest;
use App\Http\Controllers\Controller;
use App\Http\Resources\Customer\ServiceRequestResource;
use App\Http\Resources\Customer\ServiceRequestDetailsResource;
use App\Http\Requests\Customer\StoreServiceRequestRequest;
use App\Services\CRUDRequest\ServiceRequestService;

class ServiceRequestController extends Controller
{
    public function __construct(
        private ServiceRequestService $service
    ) {}

  public function store(StoreServiceRequestRequest $request)
{
    $serviceRequest = $this->service->store(
        auth()->user(),
        $request->validated()
    );

    return response()->json([
        'message' => 'Request created successfully',
        'data' => new ServiceRequestResource($serviceRequest),
    ], 201);
}

public function allRequest()
{
    $requests = $this->service->allRequest(
        auth()->user()
    );

    return ServiceRequestResource::collection($requests);
}


public function showRequest(ServiceRequest $serviceRequest)
{
    $request = $this->service->showRequest(
        auth()->user(),
        $serviceRequest
    );

    return new ServiceRequestDetailsResource($request);
}






}