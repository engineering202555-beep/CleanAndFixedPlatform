<?php

namespace App\Http\Resources\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SubscriptionProviderResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'                => $this->id,
            'provider_name'     => trim($this->serviceProvider->user->first_name.' '.$this->serviceProvider->user->last_name),
            'plan_name'         => $this->subscription->type === 'free' ? 'مجانية' : 'مدفوعة',
            'plan_type'         => $this->subscription->type,
            'starts_at'         => $this->starts_at?->toDateTimeString(),
            'ends_at'           => $this->ends_at?->toDateTimeString(),
            'status'            => $this->status,
            'used_requests'     => (int) $this->used_requests,
            // requests_limit هون Snapshot وقت التفعيل، مش القيمة
            // الحالية بجدول subscriptions (ممكن تكون تغيّرت لاحقاً).
            'requests_limit'    => $this->requests_limit,
            'price_paid'        => $this->price_paid !== null ? (float) $this->price_paid : null,
            'is_complimentary'  => (bool) $this->is_complimentary,
        ];
    }
}
