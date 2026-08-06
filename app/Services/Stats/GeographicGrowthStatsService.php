<?php

namespace App\Services\Stats;

use App\Models\ServiceArea;
use App\Models\ServiceProvider;
use App\Models\ServiceRequest;
use App\Traits\BuildsMonthlyGrowth;
use Illuminate\Support\Collection;

class GeographicGrowthStatsService
{
    /**
     * استخدمت resolvePeriod() بس من Trait BuildsMonthlyGrowth (الموجود
     * أصلاً بالمشروع من شغل سابق)، مش fillMonthlyGaps() لأنها مبنية
     * لمقياس واحد بس، وهون عندنا 3 مقاييس بنفس الوقت لكل شهر —
     * فعبّيت الفجوات يدوياً هون بدل ما "أكسر" الـ Trait لغرض مختلف.
     */
    use BuildsMonthlyGrowth {
        fillMonthlyGaps as protected;
    }

    public function getMonthlyGrowth(array $filters = []): Collection
    {
        [$start, $end] = $this->resolvePeriod($filters);

        $newAreas = $this->countByMonth(ServiceArea::query(), $start, $end);
        $newProviders = $this->countByMonth(ServiceProvider::query(), $start, $end);
        $newRequests = $this->countByMonth(ServiceRequest::query(), $start, $end);

        $months = collect(
            \Carbon\CarbonPeriod::create($start->copy()->startOfMonth(), '1 month', $end->copy()->startOfMonth())
        );

        return $months->map(function ($date) use ($newAreas, $newProviders, $newRequests) {
            $key = $date->format('Y-m');

            return [
                'month'              => $key,
                'new_areas_count'    => (int) ($newAreas[$key] ?? 0),
                'new_providers_count' => (int) ($newProviders[$key] ?? 0),
                'new_requests_count' => (int) ($newRequests[$key] ?? 0),
            ];
        })->values();
    }

    private function countByMonth($query, $start, $end): Collection
    {
        return $query
            ->selectRaw("DATE_FORMAT(created_at, '%Y-%m') as month, COUNT(*) as count")
            ->whereBetween('created_at', [$start, $end])
            ->groupBy('month')
            ->get()
            ->pluck('count', 'month');
    }
}
