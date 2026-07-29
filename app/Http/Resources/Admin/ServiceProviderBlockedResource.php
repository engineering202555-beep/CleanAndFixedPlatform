<?php

namespace App\Http\Resources\Admin;

use Carbon\CarbonInterface;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ServiceProviderBlockedResource extends JsonResource
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
            'full_name' => $this->user->first_name . ' ' . $this->user->last_name,
            'phone_number' => $this->user->phone_number,
            'category'            => $this->serviceCategory->name,
            'area'                => $this->serviceArea->city . ' ' . $this->serviceArea->area_name,
            'inspection_price'    => (float) $this->inspection_price,
            'bio'                 => $this->bio,
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
            'availability_status' => $this->availability_status,
            'account_status'      => $this->account_status,
            'block_reason'        => $this->when(
                $this->account_status === 'blocked',
                $this->block_reason
            ),
            'blocked_until'       => $this->when(
                $this->account_status === 'blocked',
                fn () => $this->blocked_until?->toDateTimeString()
            ),
            'remaining_block_time' => $this->blocked_until
                ? now()->diffForHumans($this->blocked_until, [
                    'parts' => 2,
                    'syntax' => CarbonInterface::DIFF_ABSOLUTE,
                ])
                : null,
            'joined_at'           => $this->created_at->toDateTimeString(),
        ];
    }
}
