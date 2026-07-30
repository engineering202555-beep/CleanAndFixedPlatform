<?php

namespace App\Services\LDAOffer;

use App\Models\Customer;
use App\Models\ServiceRequest;
use App\Models\User;
use App\Models\offer;
use Illuminate\Validation\ValidationException;

class OfferService
{
    public function index(User $user, ServiceRequest $serviceRequest)
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




    public function show(User $user, Offer $offer)
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


}