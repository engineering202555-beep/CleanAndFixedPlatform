<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Http\Resources\Customer\CustomerResource;
use App\Services\Customer\ProfileService;
use Illuminate\Http\Request;
use App\Http\Requests\Customer\UpdateCustomerProfileImageRequest;
use App\Http\Requests\Customer\UpdateCustomerProfileRequest;
class ProfileController extends Controller
{
    public function __construct(
        private ProfileService $profileService
    ) {}

    public function showProfileCustomer()
{
    return response()->json([
        'data' => new CustomerResource(
            $this->profileService->showProfileCustomer(
                auth()->user()
            )
        )
    ]);  
}

  public function updateProfileCustomer(
    UpdateCustomerProfileRequest $request
) {
    $customer = $this->profileService->updateProfileCustomer(
        auth()->user(),
        $request->validated()
    );

    return response()->json([
        'message' => 'Profile updated successfully.',
        'data' => new CustomerResource($customer),
    ]);
}

public function updateImageProfileCustomer(
    UpdateCustomerProfileImageRequest $request
) {
    $customer = $this->profileService->updateImageProfileCustomer(
        auth()->user(),
        $request->file('profile_image')
    );

    return response()->json([
        'message' => 'Profile image updated successfully.',
        'data' => new CustomerResource($customer),
    ]);
}




}