<?php

namespace App\Services\ServiceProvider;

use App\Models\ServiceProvider;

class ServiceProviderService
{
    public function index()
    {
        return ServiceProvider::query()
            ->where('is_approved', true)
            ->latest()
            ->get();
    }
}
