<?php

namespace App\Services\Subscription;

use App\Models\SubscriptionProvider;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class SubscriptionProviderQueryService
{
    public function getAll(array $filters = []): LengthAwarePaginator
    {
        return SubscriptionProvider::query()
            ->with([
                'serviceProvider.user:id,first_name,last_name',
                'subscription:id,type,requests_per_month,duration_in_days',
            ])
            ->when($filters['status'] ?? null, function ($query, $status) {
                $query->where('status', $status);
            })
            ->when($filters['subscription_id'] ?? null, function ($query, $id) {
                $query->where('subscription_id', $id);
            })
            ->when($filters['provider_search'] ?? null, function ($query, $search) {
                $query->whereHas('serviceProvider.user', function ($userQuery) use ($search) {
                    $userQuery->where('first_name', 'like', "%{$search}%")
                        ->orWhere('last_name', 'like', "%{$search}%");
                });
            })
            ->latest()
            ->paginate($filters['per_page'] ?? 15);
    }

    public function getById(int $id): SubscriptionProvider
    {
        return SubscriptionProvider::query()
            ->with([
                'serviceProvider.user:id,first_name,last_name',
                'subscription',
            ])
            ->findOrFail($id);
    }
}
