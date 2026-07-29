<?php

namespace App\Http\Resources\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MostProviderComplainedResource extends JsonResource
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
            'service_area' => [
                'city' => $this->serviceArea->city,
                'area' => $this->serviceArea->area_name,
            ],            'experience_years'    => $this->experience_years,
            'rating'              => (float) $this->rating,
            'account_status'      => $this->account_status,
            'joined_at'           => $this->created_at->toDateTimeString(),
            'completed_requests_this_month' => $this->whenNotNull($this->completed_requests_this_month),
            'complaints_count'    => $this->whenNotNull($this->complaints_count),
        ];
    }
}
