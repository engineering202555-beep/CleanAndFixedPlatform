<?php

namespace App\Http\Resources\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProviderDistributionByCategoryResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'rank'            => $this['rank'],
            'category_id'     => $this['category_id'],
            'category_name'   => $this['category_name'],
            'providers_count' => $this['providers_count'],
            'percentage'      => $this['percentage'],
        ];
    }
}
