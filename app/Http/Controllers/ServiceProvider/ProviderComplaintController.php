<?php

namespace App\Http\Controllers\ServiceProvider;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Helpers\ApiResponse;
use App\Services\ServiceProvider\collection ;
use App\Http\Requests\ServiceProvider\Complaint\StoreProviderComplaintRequest;
use App\Http\Resources\ServiceProvider\ProviderComplaintResource;
use App\Services\ServiceProvider\StoreProviderComplaintService;
class ProviderComplaintController extends Controller
{
   public function storeProviderComplaint(
    StoreProviderComplaintRequest $request,
    StoreProviderComplaintService $service
) {
    $complaint = $service->storeProviderComplaint(
        auth()->user(),
        $request->validated()
    );

    return ApiResponse::success(
        ProviderComplaintResource::make($complaint),
        'Complaint submitted successfully.',
        201
    );
}



public function getProviderComplaints(
    StoreProviderComplaintService $service
) {
    $complaints = $service->getProviderComplaints(
        auth()->user()
    );

    return ApiResponse::success(
        ProviderComplaintResource::collection($complaints),
        'Complaints retrieved successfully.'
    );
}



public function complaintsAgainstProvider(
    StoreProviderComplaintService $service
) {
    $complaints = $service->getComplaintsAgainstProvider(
        auth()->user()
    );

    return ApiResponse::success(
        ProviderComplaintResource::collection($complaints),
        'Complaints against you retrieved successfully.'
    );
}
}
