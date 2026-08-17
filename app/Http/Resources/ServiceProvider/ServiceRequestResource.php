<?php

namespace App\Http\Resources\ServiceProvider;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ServiceRequestResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'             => $this->id,
            'request_type'   => $this->request_type,
            'description'    => $this->description,
            'status'         => $this->status,
            'is_urgent'      => (bool) $this->is_urgent,
            'category'       => $this->whenLoaded('serviceCategory', fn () => $this->serviceCategory->name),
            'area'           => $this->whenLoaded('serviceArea', fn () => [
                'area_name' => $this->serviceArea->area_name,
                'city'      => $this->serviceArea->city,
                'x'         =>$this->latitude_x,
                'y'         =>$this->longitude_y
            ]),
            'customer_name'  => $this->whenLoaded('customer', function () {
                return $this->customer?->user
                    ? trim($this->customer->user->first_name.' '.$this->customer->user->last_name)
                    : null;
            }),
            'has_my_offer'   => $this->when(isset($this->has_my_offer), (bool) $this->has_my_offer),
            'date'           =>$this->starts_at,
            'images' => $this->images->map(function ($image) {

                return [
                    'id' => $image->id,
                    'path' => $image->path,
                    'type' => $image->type,
                ];

            }),
            'created_at'     => $this->created_at->toDateTimeString(),

        ];
    }
}
