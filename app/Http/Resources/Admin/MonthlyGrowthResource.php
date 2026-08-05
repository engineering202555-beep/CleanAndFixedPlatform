<?php

namespace App\Http\Resources\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MonthlyGrowthResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'month' => $this['month'],
            'count' => (int) $this['count'],
        ];
    }
}
