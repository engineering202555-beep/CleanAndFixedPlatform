<?php

namespace App\Http\Resources\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProviderDistributionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'area_id'         => $this->id,
            'area_name'       => $this->area_name,
            'city'            => $this->city,
            'total_providers' => (int) $this->total_providers,
            // مجمّعة بالـ PHP من العلاقة المحمّلة مسبقاً (with)، بدون
            // أي استعلام إضافي لكل منطقة (منع N+1).
            'breakdown'       => $this->serviceProviders
                ->groupBy(fn ($provider) => $provider->serviceCategory->name)
                ->map(fn ($group, $categoryName) => [
                    'category_name' => $categoryName,
                    'count'         => $group->count(),
                ])
                ->values(),
        ];
    }
}
