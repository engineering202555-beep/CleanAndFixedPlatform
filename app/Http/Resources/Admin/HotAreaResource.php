<?php

namespace App\Http\Resources\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class HotAreaResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'area_id'        => $this->id,
            'area_name'      => $this->area_name,
            'city'           => $this->city,
            'requests_count' => (int) $this->requests_count,
        ];
    }
}
