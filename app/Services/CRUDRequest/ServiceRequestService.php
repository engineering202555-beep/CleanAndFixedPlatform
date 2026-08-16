<?php

namespace App\Services\CRUDRequest;

use App\Models\Customer;
use App\Models\ServiceProvider;
use App\Models\ServiceArea;
use App\Models\ServiceRequest;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ServiceRequestService
{
    public function store(User $user, array $data): ServiceRequest
    {
        return DB::transaction(function () use ($user, $data) {

            /*
            ============================================
            1- جلب بيانات الزبون
            ============================================
            */

          $customer = Customer::where('user_id', $user->id)->firstOrFail();

            /*
            ============================================
            2- التحقق من الحظر  تم
            ============================================
            */

            if (
                $customer->status === 'blocked' &&
                $customer->blocked_until &&
                now()->lt($customer->blocked_until)
            ) {

                throw ValidationException::withMessages([
                    'customer' => [
                        'Your account is blocked.'
                    ]
                ]);
            }

            /*
            ============================================
            3- معالجة الطلب المستعجل    تم
            ============================================
            */

            if ($data['is_urgent']) {

               
                

                if ($customer->counter_urgent_requests_during_day >= 2) {

                    throw ValidationException::withMessages([
                        'urgent_request' => [
                            'You reached todays urgent request limit.'
                        ]
                    ]);
                }

                $customer->increment('counter_urgent_requests_during_day');
            }

            /*
            ============================================
            4- التأكد من عدم وجود طلب نشط
            لنفس الخدمة   تم
            ============================================
            */

            $exists = ServiceRequest::where('customer_id', $customer->id)
                ->where('service_category_id', $data['service_category_id'])
                ->whereIn('status', [

                    'pending_local',

                    'pending_global',

                    'processing',

                    'accepted',

                    'inspection_accepted',

                    'inspection_in_progress',

                    'fault_detected',

                    'scheduled',

                    'in_progress'

                ])

                ->exists();

            if ($exists) {

                throw ValidationException::withMessages([
                    'request' => [
                        'You already have an active request for this service.'
                    ]
                ]);
            }

            /*
            ============================================
            5- تحديد موعد البداية   تم
            ============================================
            */

            $startsAt = $data['is_urgent']
                ? now()
                : $data['starts_at'];
/*
============================================
6- تحديد حالة الطلب   تم
============================================
*/

$status = 'pending_local';

$hasLocalProvider = ServiceProvider::where(
        'service_area_id',
      $data['service_area_id']
    )
    ->where('availability_status', 'available')
    ->exists();

if (!$hasLocalProvider) {

    $status = 'pending_global';
}  

  /*

        $status = $hasLocalProvider
            ? 'pending_local'
            : 'pending_global';


        /*















            /*



            ============================================
            6- إنشاء الطلب
            ============================================
            */

            $request = ServiceRequest::create([

                'customer_id' => $customer->id,

                'service_category_id' => $data['service_category_id'],

                'service_area_id' =>$data['service_area_id'],

                'request_type' => $data['request_type'],

                'status' => $status,
                'description' => $data['description'] ?? null,

                'starts_at' => $startsAt,

                'latitude_x' => $data['latitude_x'],

                'longitude_y' => $data['longitude_y'],

                'is_urgent' => $data['is_urgent'],

                'duration_in_minutes' => $data['duration_in_minutes'] ?? 60,

                'expires_at' => now()->addHour(),

            ]);

          if (!empty($data['images'])) {

    foreach ($data['images'] as $image) {

        $path = $image->store('service_requests', 'public');

        $request->images()->create([

            'path' => $path,

            'type' => 'request_damage',

        ]);
    }
}




/*
============================================
7- إرسال الطلب لمقدمي الخدمة
============================================

إذا كانت الحالة pending_local
→ يرسل الطلب لمقدمي الخدمة بنفس المنطقة.

إذا كانت الحالة pending_global
→ يرسل الطلب لجميع مقدمي الخدمة في نفس المدينة.

(سننفذها لاحقًا عند بناء نظام العروض والإشعارات)
*/
          

            return  $request;
  
    

        });
    }


public function allRequest(User $user)
{
    /*
    ============================================
    1- جلب الزبون
    ============================================
    */

    $customer = Customer::where(
        'user_id',
        $user->id
    )->firstOrFail();

    /*
    ============================================
    2- جلب جميع طلباته
    ============================================
    */

    return ServiceRequest::with([
        'serviceCategory',
        'images'
    ])
        ->where('customer_id', $customer->id)
        ->latest()
        ->get();
}



public function showRequest(User $user, ServiceRequest $serviceRequest)
{
    /*
    ============================================
    1- جلب الزبون
    ============================================
    */

    $customer = Customer::where(
        'user_id',
        $user->id
    )->firstOrFail();

    /*
    ============================================
    2- التأكد أن الطلب يعود لهذا الزبون
    ============================================
    */

    if ($serviceRequest->customer_id != $customer->id) {

        throw ValidationException::withMessages([
            'request' => [
                'This request does not belong to you.'
            ]
        ]);
    }

    /*
    ============================================
    3- تحميل العلاقات المطلوبة
    ============================================
    */

    return $serviceRequest->load([

        'serviceCategory',

        'images',

        'offers.serviceProvider.user'

    ]);
}








public function updateRequest( User $user,ServiceRequest $serviceRequest,array $data): ServiceRequest {

    return DB::transaction(function () use ( $user,$serviceRequest,$data) {

        /*
        ============================================
        1- جلب بيانات الزبون
        ============================================
        */

        $customer = Customer::where(
            'user_id',
            $user->id
        )->firstOrFail();


        /*
        ============================================
        2- التأكد أن الطلب يعود للزبون
        ============================================
        */

        if ($serviceRequest->customer_id != $customer->id) {

            throw ValidationException::withMessages([
                'request' => [
                    'This request does not belong to you.'
                ]
            ]);
        }


        /*
        ============================================
        3- التأكد أن الطلب قابل للتعديل
        ============================================
        */

        $acceptedOfferExists = $serviceRequest
            ->offers()
            ->where('status', 'accepted')
            ->exists();

        if ($acceptedOfferExists) {

            throw ValidationException::withMessages([
                'request' => [
                    'This request cannot be edited because an offer has already been accepted.'
                ]
            ]);
        }


        /*
        ============================================
        4- التأكد من حالة الطلب
        ============================================
        */

        if (!in_array($serviceRequest->status, [
            'pending_local',
            'pending_global',
            'processing',
        ])) {

            throw ValidationException::withMessages([
                'request' => [
                    'This request cannot be edited in its current status.'
                ]
            ]);
        }


        /*
        ============================================
        5- البيانات المسموح تعديلها
        ============================================
        */

        $updateData = [];

        if (array_key_exists(
            'service_category_id',
            $data
        )) {
            $updateData['service_category_id']
                = $data['service_category_id'];
        }
         if (array_key_exists(
            'service_area_id',
            $data
        )) {
            $updateData['service_area_id']
                = $data['service_area_id'];
        }

        if (array_key_exists(
            'request_type',
            $data
        )) {
            $updateData['request_type']
                = $data['request_type'];
        }

        if (array_key_exists(
            'description',
            $data
        )) {
            $updateData['description']
                = $data['description'];
        }

        if (array_key_exists(
            'starts_at',
            $data
        )) {
            $updateData['starts_at']
                = $data['starts_at'];
        }

        if (array_key_exists(
            'latitude_x',
            $data
        )) {
            $updateData['latitude_x']
                = $data['latitude_x'];
        }

        if (array_key_exists(
            'longitude_y',
            $data
        )) {
            $updateData['longitude_y']
                = $data['longitude_y'];
        }

        if (array_key_exists(
            'duration_in_minutes',
            $data
        )) {
            $updateData['duration_in_minutes']
                = $data['duration_in_minutes'];
        }


        /*
        ============================================
        6- تحديث الطلب
        ============================================
        */

        $serviceRequest->update($updateData);


        /*
        ============================================
        7- إرجاع الطلب بعد التحديث
        ============================================
        */

        return $serviceRequest->fresh([
            'customer',
            'serviceCategory',
            'serviceArea',
            'offers.serviceProvider.user',
        ]);
    });
}


public function cancelRequest(
    User $user,
    ServiceRequest $serviceRequest
): ServiceRequest {

    return DB::transaction(function () use (
        $user,
        $serviceRequest
    ) {

        /*
        ============================================
        1- جلب بيانات الزبون
        ============================================
        */

        $customer = Customer::where(
            'user_id',
            $user->id
        )->firstOrFail();


        /*
        ============================================
        2- التأكد أن الطلب يعود للزبون
        ============================================
        */

        if ($serviceRequest->customer_id != $customer->id) {

            throw ValidationException::withMessages([
                'request' => [
                    'This request does not belong to you.'
                ]
            ]);
        }


        /*
        ============================================
        3- التأكد من عدم وجود عرض مقبول
        ============================================
        */

        $acceptedOfferExists = $serviceRequest
            ->offers()
            ->where('status', 'accepted')
            ->exists();

        if ($acceptedOfferExists) {

            throw ValidationException::withMessages([
                'request' => [
                    'This request cannot be cancelled because an offer has already been accepted.'
                ]
            ]);
        }


        /*
        ============================================
        4- التأكد أن حالة الطلب تسمح بالإلغاء
        ============================================
        */

        if (!in_array($serviceRequest->status, [
            'pending_local',
            'pending_global',
            'processing',
        ])) {

            throw ValidationException::withMessages([
                'request' => [
                    'This request cannot be cancelled in its current status.'
                ]
            ]);
        }


        /*
        ============================================
        5- إلغاء الطلب
        ============================================
        */

        $serviceRequest->update([
            'status' => 'cancel_by_customer',
        ]);

$serviceRequest->offers()
    ->where('status', 'pending')
    ->update([
        'status' => 'rejected',
    ]);
        /*
        ============================================
        6- إرجاع الطلب
        ============================================
        */

        return $serviceRequest->fresh([
            'customer',
            'serviceCategory',
            'serviceArea',
            'offers.serviceProvider.user',
        ]);
    });
}















}