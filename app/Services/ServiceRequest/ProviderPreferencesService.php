<?php

namespace App\Services\ServiceRequest;

use App\Models\ServiceProvider;

class ProviderPreferencesService
{
    public function updateDoNotDisturb(ServiceProvider $provider, bool $enabled): ServiceProvider
    {
        $provider->update(['do_not_disturb' => $enabled]);

        return $provider;
    }
}
