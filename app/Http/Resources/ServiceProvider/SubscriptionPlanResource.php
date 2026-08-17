<?php

namespace App\Http\Resources\ServiceProvider;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SubscriptionPlanResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'                  => $this->id,
            'type'                => $this->type,
            'requests_per_month'  => $this->requests_per_month,
            'price'               => (float) $this->price,
            'duration_in_days'    => $this->duration_in_days,
            'description'         => $this->description,
        ];
    }
}
