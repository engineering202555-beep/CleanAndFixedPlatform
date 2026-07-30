<?php

namespace App\Http\Resources\Customer;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OfferDetailsResource extends JsonResource
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

    // معلومات مقدم الخدمة
    'service_provider' => [
        'id' => $this->serviceProvider->id,
        'name' => $this->serviceProvider->user->first_name,
        'rating' => $this->serviceProvider->average_rating,
    ],

    // معلومات الطلب
    'service_request_id' => $this->service_request_id,

    // معلومات العرض
    'price' => $this->price,
    'estimated_duration' => $this->estimated_duration,
    'status' => $this->status,
    'notes' => $this->notes,
    'starts_at' => $this->starts_at,
    'duration_in_minutes' => $this->duration_in_minutes,
    'expires_at' => $this->expires_at,

    // تواريخ السجل
    'created_at' => $this->created_at,
    'updated_at' => $this->updated_at,
];
    }
}
