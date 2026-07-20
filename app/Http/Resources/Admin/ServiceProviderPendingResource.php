<?php

namespace App\Http\Resources\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ServiceProviderPendingResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [

            'id' => $this->id,

            'full_name' => $this->user->first_name . ' ' . $this->user->last_name,

            'phone_number' => $this->user->phone_number,

            'category' => $this->serviceCategory->name,

            'inspection_price' => $this->inspection_price,

            'experience_years' => $this->experience_years,

            'working_hours'       => [
                'from' => $this->working_from?->format('H:i'),
                'to'   => $this->working_to?->format('H:i'),
            ],

            'service_area' => [
                'city' => $this->serviceArea->city,
                'area' => $this->serviceArea->area_name,
            ],

            'location' => [
                'latitude' => $this->latitude,
                'longitude' => $this->longitude,
            ],

            'profile_image' => $this->images
                ->where('type', 'profile')
                ->first()?->url,

            'documents' => $this->images
                ->where('type', 'documents')
                ->values()
                ->map(fn ($image) => [
                    'id' => $image->id,
                    'url' => $image->url,
                ]),

            'requested_at' => $this->created_at
                ->format('Y-m-d H:i'),

        ];
    }
}
