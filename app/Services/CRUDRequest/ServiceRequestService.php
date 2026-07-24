<?php

namespace App\Services\CRUDRequest;

use App\Models\Customer;
use App\Models\ServiceRequest;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ServiceRequestService
{
    public function store(User $user, array $data): array
    {
        return DB::transaction(function () use ($user, $data) {

            /*
            ============================================
            1- جلب بيانات الزبون
            ============================================
            */

            $customer = Customer::where('user_id', $user->user_id)->firstOrFail();

            /*
            ============================================
            2- التحقق من الحظر
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
            3- معالجة الطلب المستعجل
            ============================================
            */

            if ($data['is_urgent']) {

                // إذا أضفتِ last_urgent_request_date
                // هنا نعيد تصفير العداد عند بداية يوم جديد

                
                if ($customer->last_urgent_request_date != today()) {

                    $customer->counter_urgent_requests_during_day = 0;

                    $customer->last_urgent_request_date = today();
                }
                

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
            لنفس الخدمة
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
            5- تحديد موعد البداية
            ============================================
            */

            $startsAt = $data['is_urgent']
                ? now()
                : $data['starts_at'];

            /*
            ============================================
            6- إنشاء الطلب
            ============================================
            */

            $request = ServiceRequest::create([

                'customer_id' => $customer->id,

                'service_category_id' => $data['service_category_id'],

                'service_area_id' =>$customer->service_area_id,

                'request_type' => $data['request_type'],

                'status' => 'pending_local',
                'description' => $data['description'] ?? null,

                'starts_at' => $startsAt,

                'latitude_x' => $data['latitude'],

                'longitude_y' => $data['longitude'],

                'is_urgent' => $data['is_urgent'],

                'duration_in_minutes' => $data['duration_in_minutes'] ?? 60,

                'expires_at' => now()->addHour(),

            ]);

            /*
            ============================================
            7- رفع الصور
            (سنضيفه لاحقاً)
            ============================================
            */

            /*
            ============================================
            8- إرجاع النتيجة
            ============================================
            */

            return [

                'message' => 'Service request created successfully.',

                'request' => $request,

            ];

        });
    }
}