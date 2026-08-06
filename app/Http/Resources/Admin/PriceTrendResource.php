<?php

namespace App\Http\Resources\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PriceTrendResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'month'                 => $this->month,
            'average_price'         => (float) $this->average_price,
            'accepted_offers_count' => (int) $this->accepted_offers_count,
        ];
    }
}
