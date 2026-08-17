<?php

namespace App\Traits;

use App\Models\ServiceProvider;

trait ChecksProviderAvailability
{
    protected function isWithinWorkingHours(ServiceProvider $provider): bool
    {
        $now = now()->format('H:i:s');

        return $now >= $provider->working_from->format('H:i:s')
            && $now <= $provider->working_to->format('H:i:s');
    }
}
