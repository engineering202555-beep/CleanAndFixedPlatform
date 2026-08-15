<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Http\Requests\Customer\StoreComplaintRequest;
use App\Http\Resources\Customer\ComplaintResource;
use App\Services\Complaint\StoreComplaintService;

class ComplaintController extends Controller
{
    public function __construct(
        private StoreComplaintService $complaintService
    ) {
    }

    public function storeComplaint(
        StoreComplaintRequest $request
    ) {

        $complaint = $this->complaintService->storeComplaint(
            auth()->user(),
            $request->validated()
        );

        return response()->json([
            'message' => 'Complaint submitted successfully.',
            'data' => new ComplaintResource($complaint),
        ], 201);
    }
}