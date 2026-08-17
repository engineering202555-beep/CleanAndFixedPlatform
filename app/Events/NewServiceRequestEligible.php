<?php

namespace App\Events;

use App\Models\ServiceProvider;
use App\Models\ServiceRequest;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class NewServiceRequestEligible
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public readonly ServiceRequest $serviceRequest,
        public readonly ServiceProvider $provider,
    ) {
    }
}
