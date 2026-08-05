<?php

namespace App\Http\Resources\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CustomerInfoResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'             => $this->id,
            'full_name'      => trim($this->user->first_name.' '.$this->user->last_name),
            'phone_number'   => $this->user->phone_number,
            'area' => [
                'city' => $this->serviceArea->city,
                'area' => $this->serviceArea->area_name,
            ],
            'status'         => $this->status,
            'completed_requests' => $this->completed_requests_count,
            'joined_at'      => $this->created_at->toDateTimeString(),
        ];
    }
}
