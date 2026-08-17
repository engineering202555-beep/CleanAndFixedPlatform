<?php

namespace App\Http\Controllers\ServiceProvider;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Helpers\ApiResponse;
use App\Http\Requests\ServiceProvider\Profile\UpdateProviderProfileImageRequest;
use App\Http\Requests\ServiceProvider\Profile\UpdateProviderProfileRequest;
use App\Http\Resources\ServiceProvider\ProviderProfileResource;
use App\Services\ServiceProvider\ProfileServiceProviderService;
class ProfileServiceProviderController extends Controller
{
     public function __construct(
        private ProfileServiceProviderService  $profileService) {
    }
      public function showProfileServiceProvider(Request $request)
    {
          $user = $this->profileService->showProfileServiceProvider(
            $request->user()
        );

        return ProviderProfileResource::make($user);
    }
    


    public function updateProviderProfile(UpdateProviderProfileRequest $request)
{
    $user = $this->profileService->updateProviderProfile(
        $request->user(),
        $request->validated()
    );

    return ApiResponse::success(
        ProviderProfileResource::make($user),
        'تم تعديل الملف الشخصي بنجاح.'
    );
}




public function updateProfileImage(
    UpdateProviderProfileImageRequest $request
) {
    $user = auth()->user();

    $user = $this->profileService
        ->updateProviderProfileImage(
            $user,
            $request->file('profile_image')
        );

    return ApiResponse::success(
        ProviderProfileResource::make($user),
        'Profile image updated successfully.'
    );
}
    }

  

