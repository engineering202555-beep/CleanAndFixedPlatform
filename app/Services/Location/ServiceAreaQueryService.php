<?php

namespace App\Services\Location;

use App\Models\ServiceArea;
use App\Models\ServiceProvider;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class ServiceAreaQueryService
{
    /**
     * الترقيم (Pagination) هون بمستوى "المدينة" نفسها، مش بمستوى
     * صفوف المناطق الخام — كل صفحة بترجع N مدينة، كل واحدة بكل
     * مناطقها كاملة (عددها صغير طبيعياً، ما بحتاج ترقيم داخلي).
     */
    public function getCitiesWithAreas(int $perPage = 15): LengthAwarePaginator
    {
        $cityNames = ServiceArea::query()
            ->select('city')
            ->distinct()
            ->orderBy('city')
            ->paginate($perPage);

        $areasByCity = $this->loadAreasWithCounts($cityNames->pluck('city'));

        $cityNames->getCollection()->transform(function ($row) use ($areasByCity) {
            return [
                'city' => $row->city,
                'areas' => $areasByCity->get($row->city, collect())->values(),
            ];
        });

        return $cityNames;
    }

    /**
     * القيم الفريدة بس، للقائمة المنسدلة بالفرونت إند (الأدمن يختار
     * منها، ما بكتب مدينة يدوياً بمكان تاني غير "إضافة مدينة جديدة").
     */
    public function getDistinctCities(): Collection
    {
        return ServiceArea::query()
            ->select('city')
            ->distinct()
            ->orderBy('city')
            ->pluck('city');
    }

    private function loadAreasWithCounts(Collection $cityNames): Collection
    {
        return ServiceArea::query()
            ->whereIn('city', $cityNames)
            ->withCount([
                'serviceProviders',
                'customers',
                'serviceRequests',
            ])
            ->addSelect([
                // عدد أنواع الخدمات المختلفة يلي مقدمي الخدمة بهالمنطقة
                // بيقدموها فعلياً (Distinct Count عبر Subquery، مش N+1).
                'service_types_count' => ServiceProvider::query()
                    ->selectRaw('count(distinct service_category_id)')
                    ->whereColumn('service_area_id', 'service_areas.id'),
            ])
            ->get()
            ->groupBy('city');
    }
}
