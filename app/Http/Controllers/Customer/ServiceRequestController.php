<?php

namespace App\Http\Controllers\Customer;
use App\Models\ServiceRequest;
use App\Http\Controllers\Controller;
use App\Http\Resources\Customer\ServiceRequestResource;
use App\Http\Resources\Customer\ServiceRequestDetailsResource;
use App\Http\Requests\Customer\StoreServiceRequestRequest;
use App\Http\Requests\Customer\UpdateServiceRequestRequest;
use App\Services\CRUDRequest\ServiceRequestService;

use App\Services\CRUDRequest\ConfirmationService;
class ServiceRequestController extends Controller
{
    public function __construct(
        private ServiceRequestService $service,
         private ConfirmationService $confirmationService
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




public function updateRequest(
    UpdateServiceRequestRequest $request,
    ServiceRequest $serviceRequest
) {

    $serviceRequest = $this->service->updateRequest(
        auth()->user(),
        $serviceRequest,
        $request->validated()
    );

    return response()->json([
        'message' => 'Service request updated successfully.',
        'data' => new ServiceRequestResource($serviceRequest),
    ]);
}

public function cancelRequest(ServiceRequest $serviceRequest)
{
    $serviceRequest = $this->service->cancelRequest(
        auth()->user(),
        $serviceRequest
    );

    return response()->json([
        'message' => 'Service request cancelled successfully.',
        'data' => new ServiceRequestResource($serviceRequest),
    ]);
}


public function confirmService(ServiceRequest $serviceRequest)
{
    $serviceRequest = $this->confirmationService->confirmService(
        auth()->user(),
        $serviceRequest
    );

    return response()->json([
        'message' => 'Service completed successfully.',
        'data' => new ServiceRequestResource($serviceRequest),
    ]);
}


}