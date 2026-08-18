<?php

namespace App\Services\CRUDRequest;

use App\Models\Customer;
use App\Models\ServiceRequest;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ConfirmationService
{
    public function confirmService(
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
            2- التأكد أن الطلب يعود لهذا الزبون
            ============================================
            */

            if ($serviceRequest->customer_id !== $customer->id) {

                throw ValidationException::withMessages([
                    'request' => [
                        'This request does not belong to you.'
                    ]
                ]);
            }


            /*
            ============================================
            3- التأكد أن الطلب ينتظر التأكيد
            ============================================
            */

            if ($serviceRequest->status !== 'in_progress') {

                throw ValidationException::withMessages([
                    'request' => [
                        'This request cannot be confirmed in its current status.'
                    ]
                ]);
            }


            /*
            ============================================
            4- تأكيد إنجاز الخدمة
            ============================================
            */

            $serviceRequest->update([
                'status' => 'awaiting_confirmation',
            ]);


            /*
            ============================================
            5- إرجاع الطلب بعد التحديث
            ============================================
            */

            return $serviceRequest->fresh();
        });
    }
}