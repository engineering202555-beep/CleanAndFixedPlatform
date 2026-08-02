<?php

namespace App\Http\Resources\Customer;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ServiceRequestDetailsResource extends JsonResource
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

            'service_category' => [
                'id' => $this->serviceCategory->id,
                'name' => $this->serviceCategory->name,
            ],

            'service_area_id' => $this->service_area_id,

            'request_type' => $this->request_type,

            'status' => $this->status,

            'description' => $this->description,

            'is_urgent' => $this->is_urgent,

            'starts_at' => $this->starts_at,

            'duration_in_minutes' => $this->duration_in_minutes,

            'expires_at' => $this->expires_at,

            'latitude_x' => $this->latitude_x,

            'longitude_y' => $this->longitude_y,

            'images' => $this->images->map(function ($image) {

                return [
                    'id' => $image->id,
                    'path' => $image->path,
                    'type' => $image->type,
                ];

            }),

          

            

            'created_at' => $this->created_at,
        ];
    }
}



       