<?php

namespace App\Http\Resources\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ServiceProviderResource extends JsonResource
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

            'full_name' => $this->user->first_name.' '.$this->user->last_name,

            'phone' => $this->user->phone_number,

            'category' => $this->category->name,

            'service_area' => $this->area->city.' - '.$this->area->area_name,

            'inspection_price' => $this->inspection_price,

            'experience_years' => $this->experience_years,

            'rating' => $this->rating,

            'working_from' => $this->working_from,

            'working_to' => $this->working_to,

            'availability_status' => $this->availability_status,

            'account_status' => $this->account_status,

            'created_at' => $this->created_at,
        ];
    }
}
