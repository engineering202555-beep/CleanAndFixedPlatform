<?php

namespace App\Http\Resources\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ServiceProviderListResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $activeSubscription = $this->subscriptions
            ->where('status', 'active')
            ->first();

        return [

            'id' => $this->id,

            'name' => $this->user->first_name . ' ' . $this->user->last_name,

            'profile_image' => optional(
                $this->images->where('type', 'profile')->first()
            )->path,

            'category' => $this->serviceCategory->name,

            'service_area' => [
                'city' => $this->serviceArea->city,
                'area' => $this->serviceArea->area_name,
            ],

            'account_status' => $this->account_status,

            'subscription' => $activeSubscription?->subscription?->type,

            'joined_at' => $this->created_at->format('Y-m-d'),
        ];
    }
}
