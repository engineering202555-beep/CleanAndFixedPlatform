<?php

namespace App\Services\Complaint;

use App\Models\Complaint;
use App\Models\Customer;
use App\Models\User;
use App\Models\ServiceRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class StoreComplaintService
{
    public function storeComplaint(
        User $user,
        array $data
    ): Complaint {

        return DB::transaction(function () use ($user, $data) {

            /*
            ============================================
            1- جلب Customer
            ============================================
            */

            $customer = Customer::where(
                'user_id',
                $user->id
            )->firstOrFail();


            /*
            ============================================
            2- جلب Service Request
            ============================================
            */

            $serviceRequest = ServiceRequest::findOrFail(
                $data['service_request_id']
            );


            /*
            ============================================
            3- التأكد أن الطلب يعود لهذا الزبون
            ============================================
            */

            if ($serviceRequest->customer_id != $customer->id) {

                throw ValidationException::withMessages([
                    'service_request_id' => [
                        'This service request does not belong to you.'
                    ]
                ]);
            }


            /*
            ============================================
            4- البحث عن العرض المقبول
            ============================================
            */

            $acceptedOffer = $serviceRequest->offers()
                ->where('status', 'accepted')
                ->with('serviceProvider.user')
                ->first();


            /*
            ============================================
            5- يجب أن يكون هناك مقدم خدمة
            ============================================
            */

            if (!$acceptedOffer) {

                throw ValidationException::withMessages([
                    'service_request_id' => [
                        'This request has no accepted service provider.'
                    ]
                ]);
            }


            /*
            ============================================
            6- تحديد مقدم الخدمة تلقائيًا
            ============================================
            */

            $againstUserId =
                $acceptedOffer->serviceProvider->user_id;


            /*
            ============================================
            7- إنشاء الشكوى
            ============================================
            */

            $complaint = Complaint::create([

                // صاحب الشكوى
                'user_id' => $user->id,

                // مقدم الخدمة الذي الشكوى ضده
                'against_user_id' => $againstUserId,

                // الطلب المرتبط بالشكوى
                'service_request_id' => $serviceRequest->id,

                // بيانات الشكوى
                'reason' => $data['reason'],

                'description' => $data['description'],

                // الحالة الابتدائية
                'status' => 'pending',

                // يحددها Admin لاحقًا
                'admin_notes' => null,
            ]);


            /*
            ============================================
            8- إرجاع الشكوى مع العلاقات
            ============================================
            */

            return $complaint->load([
                'user',
                'againstUser',
                'serviceRequest',
            ]);
        });
    }
}