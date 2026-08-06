<?php

namespace App\Http\Resources\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AreaResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'                  => $this->id,
            'area_name'           => $this->area_name,
            'city'                => $this->city,
            'providers_count'     => (int) $this->service_providers_count,
            'requests_count'      => (int) $this->service_requests_count,
            'customers_count'     => (int) $this->customers_count,
            'service_types_count' => (int) $this->service_types_count,
        ];
    }
}
