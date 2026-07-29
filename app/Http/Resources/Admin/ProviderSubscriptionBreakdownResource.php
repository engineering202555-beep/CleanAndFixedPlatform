<?php

namespace App\Http\Resources\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProviderSubscriptionBreakdownResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'                      => $this->id,
            'full_name'               => trim($this->user->first_name.' '.$this->user->last_name),
            'total_subscriptions'     => $this->subscriptions->count(),
            'subscriptions_breakdown' => $this->subscriptions
                ->groupBy('subscription_id')
                ->map(function ($group) {
                    $plan = $group->first()->subscription;

                    return [
                        'subscription_id'   => $plan->id,
                        'subscription_name' => $plan->type,
                        'times_subscribed'  => $group->count(),
                    ];
                })
                ->values(),
        ];
    }
}
