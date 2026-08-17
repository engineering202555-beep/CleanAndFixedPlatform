<?php

namespace App\Services\ServiceRequest;

use App\Models\ServiceProvider;
use App\Models\Subscription;
use App\Models\SubscriptionProvider;
use Illuminate\Database\Eloquent\Collection;

class ProviderSubscriptionQueryService
{
    /**
     * بس الخطط المفعّلة (is_active=true) — خطة عطّلها الأدمن ما
     * لازم تظهر كخيار جديد لمقدم خدمة، حتى لو كان أصلاً مشترك
     * فيها قديماً.
     */
    public function getAvailablePlans(): Collection
    {
        return Subscription::query()->where('is_active', true)->get();
    }

    public function getCurrentSubscription(ServiceProvider $provider): ?SubscriptionProvider
    {
        return SubscriptionProvider::query()
            ->where('service_provider_id', $provider->id)
            ->whereIn('status', ['active', 'pending_payment'])
            ->with('subscription:id,type')
            ->latest()
            ->first();
    }
}
