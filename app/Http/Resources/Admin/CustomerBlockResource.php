<?php

namespace App\Http\Resources\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CustomerBlockResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'                   => $this->id,
            'full_name'            => trim($this->user->first_name.' '.$this->user->last_name),
            'phone_number'         => $this->user->phone_number,
            'service_area' => [
                'city' => $this->serviceArea->city,
                'area' => $this->serviceArea->area_name,
            ],
            'block_reason'         => $this->block_reason,
            'blocked_until'        => $this->blocked_until?->toDateTimeString(),
            'remaining_block_days' => (int) max(0, ceil(now()->diffInDays($this->blocked_until, false))),
        ];
    }
}
