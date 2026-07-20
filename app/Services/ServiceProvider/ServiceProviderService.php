<?php

namespace App\Services\ServiceProvider;

use App\Models\ServiceProvider;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ServiceProviderService
{
    public function getApprovedProviders()
    {
        return ServiceProvider::with([
            'user',
            'serviceCategory',
            'serviceArea',
            'images',
            'subscriptions.subscription',
        ])
            ->where('is_approved', true)
            ->latest()
            ->get();
    }

    public function getApprovedProviderDetails(ServiceProvider $provider)
    {
        $provider->load([
            'user',
            'serviceCategory',
            'serviceArea',
            'images',
            'subscriptions.subscription',
        ]);

        if (! $provider->is_approved) {
            throw new NotFoundHttpException(
                'Service provider not found.'
            );
        }

        return $provider;
    }

    public function getPendingProviders()
    {
        return ServiceProvider::query()
            ->with([
                'user',
                'serviceCategory',
                'serviceArea',
                'images',
            ])
            ->where('is_approved', false)
            ->where('account_status', 'pending')
            ->orderBy('created_at')
            ->get();
    }
}
