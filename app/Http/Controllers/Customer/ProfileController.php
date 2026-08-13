<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Http\Resources\Customer\ProfileResource;
use App\Services\Customer\ProfileService;
use Illuminate\Http\Request;
use App\Http\Requests\Customer\UpdateProfileImageRequest;
use App\Http\Requests\Customer\UpdateProfileRequest;
class ProfileController extends Controller
{
    public function __construct(
        private ProfileService $profileService
    ) {}

    public function showProfileCustomer(Request $request)
    {
        $customer = $this->profileService->showProfileCustomer(
            $request->user()
        );

        return new ProfileResource($customer);
    }

    public function updateProfileCustomer(UpdateProfileRequest $request)
{
    $customer = $this->profileService->updateProfileCustomer(
        $request->user(),
        $request->validated()
    );

    return new ProfileResource($customer);
}

public function updateImageProfileCustomer(UpdateProfileImageRequest $request)
{
    $customer = $this->profileService->updateImageProfileCustomer(
        $request->user(),
        $request->file('image')
    );

    return new ProfileResource($customer);
}




}