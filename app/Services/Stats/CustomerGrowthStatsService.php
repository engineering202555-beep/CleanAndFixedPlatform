<?php

namespace App\Services\Stats;

use App\Models\Customer;
use App\Traits\BuildsMonthlyGrowth;
use Illuminate\Support\Collection;

class CustomerGrowthStatsService
{
    use BuildsMonthlyGrowth;

    public function getMonthlyGrowth(array $filters = []): Collection
    {
        [$start, $end] = $this->resolvePeriod($filters);

        $cacheKey = 'stats:customers-growth:'.md5(serialize([$start->toDateString(), $end->toDateString()]));

        return $this->rememberStats($cacheKey, $end, function () use ($start, $end) {
            $results = Customer::query()
                ->selectRaw("DATE_FORMAT(created_at, '%Y-%m') as month, COUNT(*) as count")
                ->whereBetween('created_at', [$start, $end])
                ->groupBy('month')
                ->get();

            return $this->fillMonthlyGaps($results, $start, $end);
        });
    }
}
