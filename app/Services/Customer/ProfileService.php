<?php

namespace App\Services\Customer;

use App\Models\Customer;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\UploadedFile;
class ProfileService
{
    public function showProfileCustomer(User $user): Customer
    {
        return Customer::where('user_id', $user->id)
            ->with([
                'user',
               // 'user.images',
                'serviceArea',
            ])
            ->firstOrFail();
    }

    public function updateProfileCustomer(User $user, array $data): Customer
    {
        return DB::transaction(function () use ($user, $data) {

            $customer = Customer::where('user_id', $user->id)
                ->firstOrFail();

            // بيانات موجودة في users
            $userData = [];

            if (array_key_exists('first_name', $data)) {
                $userData['first_name'] = $data['first_name'];
            }

           

            if (!empty($userData)) {
                $user->update($userData);
            }

            // بيانات موجودة في customers
            if (array_key_exists('service_area_id', $data)) {
                $customer->update([
                    'service_area_id' => $data['service_area_id'],
                ]);
            }

            return $customer->load([
                'user',
             //   'user.images',
                'serviceArea',
            ]);
        });
    }


public function updateImageProfileCustomer(
    User $user,
    UploadedFile $image
): Customer {

    return DB::transaction(function () use ($user, $image) {

        $customer = Customer::where('user_id', $user->id)
            ->firstOrFail();

        // حذف صورة البروفايل القديمة
        $oldImage = $user->images()
            ->where('type', 'profile')
            ->first();

        if ($oldImage) {
            $oldImage->delete();
        }

        // تخزين الصورة الجديدة
        $path = $image->store('profiles', 'public');

        // إنشاء سجل في images
        $user->images()->create([
            'path' => $path,
            'type' => 'profile',
        ]);

        return $customer->load([
            'user',
            'user.images',
            'serviceArea',
        ]);
    });
}





}