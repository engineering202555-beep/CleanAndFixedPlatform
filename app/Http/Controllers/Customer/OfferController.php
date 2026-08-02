<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Http\Resources\Customer\OfferResource;
use App\Http\Resources\Customer\OfferDetailsResource;
use App\Models\ServiceRequest;
use App\Models\offer;
use App\Services\LDAOffer\OfferService;

class OfferController extends Controller
{
    public function __construct(
        private OfferService $offerService
    ) {}

    public function allOffer(ServiceRequest $serviceRequest)
    {
        $offers = $this->offerService->allOffer(
            auth()->user(),
            $serviceRequest
        );

        return OfferResource::collection($offers);
    }

    
public function showOffer(Offer $offer)
{
    return new OfferDetailsResource(

        $this->offerService->showOffer(
            auth()->user(),
            $offer
        )

    );
}

public function acceptOffer(Offer $offer)
{
    return response()->json(

        $this->offerService->acceptOffer(
            auth()->user(),
            $offer
        )

    );
}





}