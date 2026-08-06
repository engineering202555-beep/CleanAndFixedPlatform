<?php

namespace App\Services\Stats;

use App\Models\ServiceArea;
use App\Models\ServiceRequest;
use App\Traits\BuildsMonthlyGrowth;
use Illuminate\Support\Collection;

class HotAreaStatsService
{

    private const DEFAULT_DAYS = 30;

    /**
     * ترتيب المناطق حسب عدد الطلبات ضمن نافذة زمنية (افتراضياً آخر
     * 30 يوم) بدل All-Time، لتفادي انحياز الترتيب دائماً للمناطق
     * الأقدم تسجيلاً بغض النظر عن نشاطها الحالي.
     */
    public function getHotAreas(array $filters = []): Collection
    {
        $days = $filters['days'] ?? self::DEFAULT_DAYS;
        $limit = $filters['limit'] ?? 10;

        return ServiceArea::query()
            ->withCount([
                'serviceRequests as requests_count' => function ($query) use ($days) {
                    $query->where('created_at', '>=', now()->subDays($days));
                },
            ])
            ->having('requests_count', '>', 0)
            ->orderByDesc('requests_count')
            ->limit($limit)
            ->get();
    }

    /**
     * Grid-Based Density Bucketing: تقريب الإحداثيات لعدد منازل عشرية
     * محدد (Grid Cell)، وتجميع عدد الطلبات بكل خلية. النتيجة خفيفة
     * ({lat, lng, count} بس) وجاهزة لأي مكتبة Heatmap بالفرونت إند
     * بدون أي معالجة جغرافية إضافية منه.
     */
    public function getDensityMap(array $filters = []): Collection
    {
        $precision = $filters['precision'] ?? 3;
        $days = $filters['days'] ?? null;

        return ServiceRequest::query()
            ->selectRaw("
                ROUND(latitude_x, {$precision}) as lat,
                ROUND(longitude_y, {$precision}) as lng,
                COUNT(*) as count
            ")
            ->when($filters['service_area_id'] ?? null, function ($query, $areaId) {
                $query->where('service_area_id', $areaId);
            })
            ->when($days, function ($query, $days) {
                $query->where('created_at', '>=', now()->subDays($days));
            })
            ->groupBy('lat', 'lng')
            ->orderByDesc('count')
            ->get();
    }
}
