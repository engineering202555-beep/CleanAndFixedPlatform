<?php

namespace App\Services\ServiceProvider;

use App\Models\ServiceProvider;
use App\Models\User;
use App\Services\Image\ImageUploadService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
class ProfileServiceProviderService

{

public function __construct(
    private readonly ImageUploadService $imageUploadService
) {
}
    public function showProfileServiceProvider(User $user): User
    {
 return $user->load([
        'serviceProvider.serviceCategory',
        'serviceProvider.serviceArea',
        'serviceProvider.images',
    ]);
}


public function updateProviderProfile(User $user, array $data): User
    {
        return DB::transaction(function () use ($user, $data) {

            $provider = $user->serviceProvider;

            /*
            |--------------------------------------------------------------------------
            | بيانات موجودة في users
            |--------------------------------------------------------------------------
            */

            $userData = [];

            if (array_key_exists('first_name', $data)) {
                $userData['first_name'] = $data['first_name'];
            }

            if (array_key_exists('last_name', $data)) {
                $userData['last_name'] = $data['last_name'];
            }

            if (!empty($userData)) {
                $user->update($userData);
            }

            /*
            |--------------------------------------------------------------------------
            | بيانات موجودة في service_providers
            |--------------------------------------------------------------------------
            */

            $providerFields = [
                'service_category_id',
                'service_area_id',
                'inspection_price',
                'bio',
                'experience_years',
                'working_from',
                'working_to',
                'latitude',
                'longitude',
            ];

            $providerData = [];

            foreach ($providerFields as $field) {
                if (array_key_exists($field, $data)) {
                    $providerData[$field] = $data[$field];
                }
            }

            if (!empty($providerData)) {
                $provider->update($providerData);
            }

            /*
            |--------------------------------------------------------------------------
            | إعادة تحميل البيانات اللازمة للـResource
            |--------------------------------------------------------------------------
            */

            return $user->load([
                'serviceProvider.serviceCategory',
                'serviceProvider.serviceArea',
                'serviceProvider.images',
            ]);
        });
    }



public function updateProviderProfileImage(
    User $user,
    UploadedFile $image
): User {
    return DB::transaction(function () use ($user, $image) {

        $provider = $user->serviceProvider;

        if (!$provider) {
            abort(404, 'Service provider not found.');
        }

        /*
        |--------------------------------------------------------------------------
        | حذف صورة البروفايل القديمة
        |--------------------------------------------------------------------------
        */

        $oldImage = $provider->images()
            ->where('type', 'profile')
            ->latest('id')
            ->first();

        if ($oldImage) {
            $oldImage->delete();
        }

        /*
        |--------------------------------------------------------------------------
        | رفع الصورة الجديدة
        |--------------------------------------------------------------------------
        */

        $this->imageUploadService->upload(
            $provider,
            $image,
            'profile'
        );

        /*
        |--------------------------------------------------------------------------
        | إعادة تحميل البيانات
        |--------------------------------------------------------------------------
        */

        return $user->load([
            'serviceProvider.serviceCategory',
            'serviceProvider.serviceArea',
            'serviceProvider.images',
        ]);
    });
}

















}


