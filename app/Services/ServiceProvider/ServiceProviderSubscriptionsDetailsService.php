<?php

namespace App\Services\ServiceProvider;

use App\Models\ServiceProvider;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ServiceProviderSubscriptionsDetailsService
{
    /**
     * كل مقدم خدمة مع كامل سجل اشتراكاته (subscriptions.subscription)
     * محمّل مسبقاً بضربة استعلام وحدة (Eager Loading)، والتجميع
     * (كم مرة اشترك بكل نوع) بيصير بالـ Resource، مش هون.
     */
    public function getSubscriptionBreakdown(int $perPage = 15): LengthAwarePaginator
    {
        return ServiceProvider::query()
            ->with([
                'user:id,first_name,last_name',
                'subscriptions.subscription',
            ])
            ->paginate($perPage);
    }
}
