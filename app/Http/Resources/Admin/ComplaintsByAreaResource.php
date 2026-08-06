<?php

namespace App\Http\Resources\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ComplaintsByAreaResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'rank'             => $this['rank'],
            'area_id'          => $this['area_id'],
            'area_name'        => $this['area_name'],
            'city'             => $this['city'],
            'complaints_count' => $this['complaints_count'],
            'percentage'       => $this['percentage'],
        ];
    }
}
