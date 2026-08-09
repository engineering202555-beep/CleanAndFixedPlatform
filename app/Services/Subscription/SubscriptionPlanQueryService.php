<?php

namespace App\Services\Subscription;

use App\Models\Subscription;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class SubscriptionPlanQueryService
{
    public function getAll(array $filters = []): LengthAwarePaginator
    {
        return Subscription::query()
            ->withCount([
                'providerSubscriptions as active_subscribers_count' => function ($query) {
                    $query->where('status', 'active');
                },
                'providerSubscriptions as total_subscribers_count',
            ])
            ->when($filters['type'] ?? null, function ($query, $type) {
                $query->where('type', $type);
            })
            ->when(array_key_exists('is_active', $filters), function ($query) use ($filters) {
                $query->where('is_active', (bool) $filters['is_active']);
            })
            ->latest()
            ->paginate($filters['per_page'] ?? 15);
    }

    public function getById(int $id): Subscription
    {
        return Subscription::query()
            ->withCount([
                'providerSubscriptions as active_subscribers_count' => function ($query) {
                    $query->where('status', 'active');
                },
                'providerSubscriptions as total_subscribers_count',
            ])
            ->findOrFail($id);
    }
}
