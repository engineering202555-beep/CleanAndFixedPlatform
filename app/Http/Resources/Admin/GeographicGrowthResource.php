<?php

namespace App\Http\Resources\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class GeographicGrowthResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'month'               => $this['month'],
            'new_areas_count'     => $this['new_areas_count'],
            'new_providers_count' => $this['new_providers_count'],
            'new_requests_count'  => $this['new_requests_count'],
        ];
    }
}
