<?php

namespace App\Http\Resources\Customer;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ServiceRequestResource extends JsonResource
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

            'customer_id' => $this->customer_id,

            'service_category_id' => $this->service_category_id,

            'service_area_id' => $this->service_area_id,

            'request_type' => $this->request_type,

            'status' => $this->status,

            'description' => $this->description,

            'starts_at' => $this->starts_at,

            'latitude_x' => $this->latitude_x,

            'longitude_y' => $this->longitude_y,

            'is_urgent' => $this->is_urgent,

            'duration_in_minutes' => $this->duration_in_minutes,

            'expires_at' => $this->expires_at,

            'created_at' => $this->created_at,

        ];
    }
    }

