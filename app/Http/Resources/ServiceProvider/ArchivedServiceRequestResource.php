<?php

namespace App\Http\Resources\ServiceProvider;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ArchivedServiceRequestResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'            => $this->id,
            'description'   => $this->description,
            'category'      => $this->serviceCategory?->name,
            'area'          => $this->serviceArea?->area_name,
            'city'          => $this->serviceArea?->city,
            'customer_name' => $this->customer?->user
                ? trim($this->customer->user->first_name.' '.$this->customer->user->last_name)
                : null,
            'completed_at'  => $this->updated_at->toDateTimeString(),
            // review علاقة hasOne — ممكن تكون null لو الزبون ما قيّم
            // بعد، فبنرجعها null كاملة بدل ما نفترض وجودها دايماً.
            'review'        => $this->whenLoaded('review', function () {
                return $this->review ? [
                    'rating'     => (int) $this->review->provider_rating,
                    'comment'    => $this->review->comment,
                    'reviewed_at' => $this->review->created_at->toDateTimeString(),
                ] : null;
            }),
        ];
    }
}
