<?php

namespace App\Services\Stats;

use App\Models\ServiceCategory;
use Illuminate\Support\Collection;

class ServiceCategoryStatsService
{
    /**
     * العدّ محسوب بـ withCount + closure (Subquery فرعي بنفس استعلام
     * SQL الرئيسي)، مش بجلب كل الطلبات وعدّها بالكود.
     */
    public function getMostRequested(array $filters = []): Collection
    {
        $categories = ServiceCategory::query()
            ->withCount([
                'serviceRequests as requests_count' => function ($query) use ($filters) {
                    $query
                        ->when($filters['date_from'] ?? null, function ($q, $date) {
                            $q->whereDate('created_at', '>=', $date);
                        })
                        ->when($filters['date_to'] ?? null, function ($q, $date) {
                            $q->whereDate('created_at', '<=', $date);
                        })
                        ->when($filters['area_id'] ?? null, function ($q, $areaId) {
                            $q->where('service_area_id', $areaId);
                        })
                        ->when($filters['city'] ?? null, function ($q, $city) {
                            $q->whereHas('serviceArea', function ($areaQuery) use ($city) {
                                $areaQuery->where('city', $city);
                            });
                        });
                },
            ])
            ->having('requests_count', '>', 0)
            ->orderByDesc('requests_count')
            ->get();

        $total = $categories->sum('requests_count');

        return $categories->values()->map(function ($category, $index) use ($total) {
            return [
                'rank'           => $index + 1,
                'category_id'    => $category->id,
                'category_name'  => $category->name,
                'requests_count' => (int) $category->requests_count,
                'percentage'     => $total > 0 ? round(($category->requests_count / $total) * 100, 2) : 0,
            ];
        })->values();
    }

    public function getProviderDistribution(): Collection
    {
        $categories = ServiceCategory::query()
            ->withCount('serviceProviders as providers_count')
            ->having('providers_count', '>', 0)
            ->orderByDesc('providers_count')
            ->get();

        $total = $categories->sum('providers_count');

        return $categories->values()->map(function ($category, $index) use ($total) {
            return [
                'rank'            => $index + 1,
                'category_id'     => $category->id,
                'category_name'   => $category->name,
                'providers_count' => (int) $category->providers_count,
                'percentage'      => $total > 0 ? round(($category->providers_count / $total) * 100, 2) : 0,
            ];
        })->values();
    }
}
