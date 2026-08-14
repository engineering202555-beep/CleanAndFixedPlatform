<?php

namespace App\Http\Resources\Customer;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CustomerResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,

            'first_name' => $this->user?->first_name,
            'last_name' => $this->user?->last_name,

            'phone' => $this->user?->phone_number,

            'profile_image' => $this->profile_image
                ? asset('storage/' . $this->profile_image)
                : null,

            'service_area' => $this->serviceArea
                ? [
                    'id' => $this->serviceArea->id,
                    'city' => $this->serviceArea->city,
                    'area_name' => $this->serviceArea->area_name,
                ]
                : null,
        ];
    }
}