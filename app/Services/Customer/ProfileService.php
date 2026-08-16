<?php

namespace App\Services\Customer;

use App\Models\Customer;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;

class ProfileService
{
    /**
     * عرض ملف العميل
     */
    public function showProfileCustomer(User $user): Customer
    {
        return Customer::where('user_id', $user->id)
            ->with([
                'user',
                'serviceArea',
            ])
            ->firstOrFail();
    }

    /**
     * تعديل بيانات الملف الشخصي
     */
    public function updateProfileCustomer(
        User $user,
        array $data
    ): Customer {

        return DB::transaction(function () use ($user, $data) {

            $customer = Customer::where('user_id', $user->id)
                ->firstOrFail();

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
            | بيانات موجودة في customers
            |--------------------------------------------------------------------------
            */

            $customerData = [];

            if (array_key_exists('service_area_id', $data)) {
                $customerData['service_area_id'] =
                    $data['service_area_id'];
            }

            if (!empty($customerData)) {
                $customer->update($customerData);
            }

            return $customer->load([
                'user',
                'serviceArea',
            ]);
        });
    }

    /**
     * تعديل صورة البروفايل
     */
    public function updateImageProfileCustomer(
        User $user,
        UploadedFile $image
    ): Customer {

        return DB::transaction(function () use ($user, $image) {

            $customer = Customer::where('user_id', $user->id)
                ->firstOrFail();

            /*
            |--------------------------------------------------------------------------
            | حذف الصورة القديمة إن وجدت
            |--------------------------------------------------------------------------
            */

            if ($customer->profile_image) {

                $oldPath = storage_path(
                    'app/public/' . $customer->profile_image
                );

                if (file_exists($oldPath)) {
                    unlink($oldPath);
                }
            }

            /*
            |--------------------------------------------------------------------------
            | تخزين الصورة الجديدة
            |--------------------------------------------------------------------------
            */

            $path = $image->store('profiles', 'public');

            /*
            |--------------------------------------------------------------------------
            | تحديث customer
            |--------------------------------------------------------------------------
            */

            $customer->update([
                'profile_image' => $path,
            ]);

            return $customer->load([
                'user',
                'serviceArea',
            ]);
        });
    }
}