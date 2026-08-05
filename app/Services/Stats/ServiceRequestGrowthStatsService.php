<?php

namespace App\Services\Stats;

use App\Models\ServiceRequest;
use App\Traits\BuildsMonthlyGrowth;
use Illuminate\Support\Collection;

class ServiceRequestGrowthStatsService
{
    use BuildsMonthlyGrowth;

    public function getMonthlyGrowth(array $filters = []): Collection
    {
        [$start, $end] = $this->resolvePeriod($filters);

        $cacheKey = 'stats:service-requests-growth:'.md5(serialize($filters));

        return $this->rememberStats($cacheKey, $end, function () use ($start, $end, $filters) {
            $results = ServiceRequest::query()
                ->selectRaw("DATE_FORMAT(created_at, '%Y-%m') as month, COUNT(*) as count")
                ->whereBetween('created_at', [$start, $end])
                ->when($filters['request_type'] ?? null, function ($query, $type) {
                    $query->where('request_type', $type);
                })
                ->when($filters['service_category_id'] ?? null, function ($query, $categoryId) {
                    $query->where('service_category_id', $categoryId);
                })
                ->when($filters['status'] ?? null, function ($query, $status) {
                    $query->where('status', $status);
                })
                ->groupBy('month')
                ->get();

            return $this->fillMonthlyGaps($results, $start, $end);
        });
    }
}
