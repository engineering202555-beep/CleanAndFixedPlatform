<?php

namespace App\Http\Resources\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SupplyDemandResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'area_id'         => $this['area_id'],
            'area_name'       => $this['area_name'],
            'city'            => $this['city'],
            'category_id'     => $this['category_id'],
            'category_name'   => $this['category_name'],
            'requests_count'  => $this['requests_count'],
            'providers_count' => $this['providers_count'],
            'ratio'           => $this['ratio'],
            'status'          => $this['status'],
        ];
    }
}
