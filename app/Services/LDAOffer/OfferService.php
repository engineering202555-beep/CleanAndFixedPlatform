<?php

namespace App\Services\LDAOffer;

use App\Models\Customer;
use App\Models\ServiceRequest;
use App\Models\User;
use App\Models\offer;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class OfferService
{
    public function allOffer(User $user, ServiceRequest $serviceRequest)
    {
        /*
        ==========================================
        التأكد أن الطلب يعود لهذا الزبون
        ==========================================
        */

        $customer = Customer::where('user_id', $user->id)->firstOrFail();

        if ($serviceRequest->customer_id != $customer->id) {

            throw ValidationException::withMessages([
                'request' => [
                    'This request does not belong to you.'
                ]
            ]);
        }

        /*
        ==========================================
        يجب أن تكون الحالة Processing
        ==========================================
        */

        if ($serviceRequest->status != 'processing') {

            throw ValidationException::withMessages([
                'request' => [
                    'No offers available yet.'
                ]
            ]);
        }

        /*
        ==========================================
        جلب العروض
        ==========================================
        */

       return $serviceRequest->offers()

    ->with([

        'serviceProvider.user',

        'serviceProvider.reviews'

    ])

    ->orderBy('price')

    ->get();
    }




    public function showOffer(User $user, Offer $offer)
{
    $customer = Customer::where('user_id', $user->id)->firstOrFail();

    if ($offer->serviceRequest->customer_id != $customer->id) {

        throw ValidationException::withMessages([
            'offer' => [
                'Unauthorized.'
            ]
        ]);
    }

    return $offer->load([
        'serviceProvider.user',
        'serviceRequest'
    ]);
}
   


public function acceptOffer(User $user, Offer $offer)
{
    return DB::transaction(function () use ($user, $offer) {

        /*
        ==========================================
        1- جلب الزبون
        ==========================================
        */

        $customer = Customer::where(
            'user_id',
            $user->id
        )->firstOrFail();

        /*
        ==========================================
        2- التأكد أن الطلب يعود لهذا الزبون
        ==========================================
        */

        if ($offer->serviceRequest->customer_id != $customer->id) {

            throw ValidationException::withMessages([
                'offer' => [
                    'This offer does not belong to you.'
                ]
            ]);
        }

        /*
        ==========================================
        3- التأكد أن الطلب يسمح بقبول عرض
        ==========================================
        */

        if ($offer->serviceRequest->status != 'processing') {

            throw ValidationException::withMessages([
                'request' => [
                    'This request cannot accept offers.'
                ]
            ]);
        }

        /*
        ==========================================
        4- التأكد أن العرض Pending
        ==========================================
        */

        if ($offer->status != 'pending') {

            throw ValidationException::withMessages([
                'offer' => [
                    'This offer is no longer available.'
                ]
            ]);
        }

        /*
        ==========================================
        5- قبول العرض
        ==========================================
        */

        $offer->update([
            'status' => 'accepted'
        ]);

        /*
        ==========================================
        6- رفض بقية العروض
        ==========================================
        */

        Offer::where(
            'service_request_id',
            $offer->service_request_id
        )
            ->where('id', '!=', $offer->id)
            ->update([
                'status' => 'rejected'
            ]);

        /*
        ==========================================
        7- تحديث حالة الطلب
        ==========================================
        */

        $offer->serviceRequest->update([
            'status' => 'accepted'
        ]);

        /*
        ==========================================
        8- إشعار مقدم الخدمة
        (سنضيفه لاحقاً)
        ==========================================
        */

        return [

            'message' => 'Offer accepted successfully.',

            'offer_id' => $offer->id,

            'request_id' => $offer->service_request_id,

        ];

    });
}




}