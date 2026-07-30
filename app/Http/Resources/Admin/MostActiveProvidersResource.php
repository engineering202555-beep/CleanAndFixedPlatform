<?php

namespace App\Http\Resources\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MostActiveProvidersResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'                  => $this->id,
            'full_name'           => trim($this->user->first_name.' '.$this->user->last_name),
            'phone_number'        => $this->user->phone_number,
            'category'            => $this->serviceCategory->name,
            'area'                => $this->serviceArea->name,
            'experience_years'    => $this->experience_years,
            'rating'              => (float) $this->rating,
            'working_hours'       => [
                'from' => $this->working_from?->format('H:i'),
                'to'   => $this->working_to?->format('H:i'),
            ],
            'location'            => [
                'latitude'  => (float) $this->latitude,
                'longitude' => (float) $this->longitude,
            ],
            'joined_at'           => $this->created_at->toDateTimeString(),
            'completed_requests_this_month' => $this->whenNotNull($this->completed_requests_this_month),
        ];
    }
}
