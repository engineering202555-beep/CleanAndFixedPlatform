<?php

namespace App\Http\Resources\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ServiceProviderDetailsResource extends JsonResource
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

            'first_name' => $this->user->first_name,

            'last_name' => $this->user->last_name,

            'phone_number' => $this->user->phone_number,

            'service_category' => $this->serviceCategory->name,

            'service_area' => [
                'city' => $this->serviceArea->city,
                'area' => $this->serviceArea->area_name,
            ],

            'inspection_price' => $this->inspection_price,

            'bio' => $this->bio,

            'experience_years' => $this->experience_years,

            'rating' => $this->rating,

            'working_hours'       => [
                'from' => $this->working_from?->format('H:i'),
                'to'   => $this->working_to?->format('H:i'),
            ],

            'account_status' => $this->account_status,

            'availability_status' => $this->availability_status,

            'is_approved' => $this->is_approved,

            'location' => [
                'latitude' => $this->latitude,
                'longitude' => $this->longitude,
            ],

            'subscription' =>  $activeSubscription ? [
                'type' => $activeSubscription->subscription->type,
                'requests_per_month' => $activeSubscription->subscription->requests_per_month,
                'used_requests' => $activeSubscription->used_requests,
                'starts_at' => $activeSubscription->starts_at?->format('Y-m-d H:i'),
                'ends_at' => $activeSubscription->ends_at?->format('Y-m-d H:i'),
            ] : null ,

            'documents' => $this->images
                ->where('type', 'documents')
                ->pluck('path')
                ->values(),

            'profile_image' => optional(
                $this->images->firstWhere('type', 'profile')
            )->path,

        ];
    }
}
