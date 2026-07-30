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

    public function index(ServiceRequest $serviceRequest)
    {
        $offers = $this->offerService->index(
            auth()->user(),
            $serviceRequest
        );

        return OfferResource::collection($offers);
    }

    
public function show(Offer $offer)
{
    return new OfferDetailsResource(

        $this->offerService->show(
            auth()->user(),
            $offer
        )

    );
}







}