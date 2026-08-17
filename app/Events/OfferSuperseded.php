<?php

namespace App\Events;

use App\Models\Offer;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class OfferSuperseded
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public readonly Offer $offer
    ) {
    }
}
