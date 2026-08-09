<?php

namespace App\Http\Resources\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SubscriptionRevenueStatsResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'overview'        => $this['overview'],
            'monthly_trend'   => $this['monthly_trend'],
            'revenue_by_plan' => $this['revenue_by_plan'],
        ];
    }
}
