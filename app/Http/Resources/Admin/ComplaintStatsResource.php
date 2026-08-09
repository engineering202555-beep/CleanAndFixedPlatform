<?php

namespace App\Http\Resources\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ComplaintStatsResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'overview'          => $this['overview'],
            'reasons_breakdown' => $this['reasons_breakdown'],
            'top_areas'         => $this['top_areas'],
            'top_providers'     => $this['top_providers'],
        ];
    }

}
