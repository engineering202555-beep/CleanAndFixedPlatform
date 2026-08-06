<?php

namespace App\Services\Stats;

use App\Models\ServiceArea;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ProviderDistributionStatsService
{
    /**
     * لكل منطقة: تفصيل عدد مقدمي الخدمة حسب كل تصنيف + الإجمالي.
     * التفصيل حسب تصنيف بيصير بالـ Resource (مش هون) اعتماداً على
     * علاقة serviceProviders المحمّلة مسبقاً بكل تصنيفاتها دفعة واحدة.
     */
    public function getDistribution(int $perPage = 15): LengthAwarePaginator
    {
        return ServiceArea::query()
            ->with(['serviceProviders.serviceCategory:id,name'])
            ->withCount('serviceProviders as total_providers')
            ->having('total_providers', '>', 0)
            ->orderByDesc('total_providers')
            ->paginate($perPage);
    }
}
