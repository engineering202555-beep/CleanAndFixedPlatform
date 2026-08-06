<?php

namespace App\Services\Stats;

use App\Models\ServiceArea;
use App\Models\ServiceCategory;
use App\Models\ServiceProvider;
use App\Models\ServiceRequest;
use Illuminate\Support\Collection;

class SupplyDemandStatsService
{
    private const DEFAULT_DAYS = 30;
    private const SHORTAGE_RATIO_THRESHOLD = 5;
    private const SURPLUS_RATIO_THRESHOLD = 2;

    /**
     * مبنية على استعلامين مجمّعين (GROUP BY) بس، مش Loop على كل
     * منطقة×تصنيف (كانت أول نسخة مبنية هيك بالغلط، وهذا بالضبط
     * تعريف N+1 لو عندك 50 منطقة × 10 تصنيفات = 500 تكرار).
     * المؤشر محسوب بمستوى (منطقة × تصنيف) لتفادي إخفاء نقص بتصنيف
     * وفائض بتصنيف تاني ورا متوسط عام واحد للمنطقة.
     */
    public function getComparison(array $filters = []): Collection
    {
        $days = $filters['days'] ?? self::DEFAULT_DAYS;
        $categoryId = $filters['category_id'] ?? null;

        $requestCounts = $this->groupedRequestCounts($days, $categoryId);
        $providerCounts = $this->groupedProviderCounts($categoryId);

        $areas = ServiceArea::query()->get(['id', 'area_name', 'city'])->keyBy('id');
        $categories = ServiceCategory::query()
            ->when($categoryId, fn ($q, $id) => $q->where('id', $id))
            ->get(['id', 'name'])
            ->keyBy('id');

        $pairKeys = $requestCounts->keys()->merge($providerCounts->keys())->unique();

        $rows = $pairKeys->map(function (string $key) use ($requestCounts, $providerCounts, $areas, $categories) {
            [$areaId, $categoryId] = array_map('intval', explode('-', $key));

            $area = $areas->get($areaId);
            $category = $categories->get($categoryId);

            if (! $area || ! $category) {
                return null; // فلترة تصنيف معيّن ممكن تستثني بعض الصفوف
            }

            $requestsCount = $requestCounts->get($key, 0);
            $providersCount = $providerCounts->get($key, 0);

            return [
                'area_id'         => $area->id,
                'area_name'       => $area->area_name,
                'city'            => $area->city,
                'category_id'     => $category->id,
                'category_name'   => $category->name,
                'requests_count'  => $requestsCount,
                'providers_count' => $providersCount,
                'ratio'           => $providersCount > 0 ? round($requestsCount / $providersCount, 2) : null,
                'status'          => $this->classify($requestsCount, $providersCount),
            ];
        })->filter()->values();

        return $rows->sortByDesc('ratio')->values();
    }

    private function groupedRequestCounts(int $days, ?int $categoryId): Collection
    {
        return ServiceRequest::query()
            ->select('service_area_id', 'service_category_id')
            ->selectRaw('count(*) as requests_count')
            ->where('created_at', '>=', now()->subDays($days))
            ->when($categoryId, fn ($q, $id) => $q->where('service_category_id', $id))
            ->groupBy('service_area_id', 'service_category_id')
            ->get()
            ->keyBy(fn ($row) => "{$row->service_area_id}-{$row->service_category_id}")
            ->map(fn ($row) => (int) $row->requests_count);
    }

    private function groupedProviderCounts(?int $categoryId): Collection
    {
        return ServiceProvider::query()
            ->select('service_area_id', 'service_category_id')
            ->selectRaw('count(*) as providers_count')
            ->where('account_status', 'active')
            ->when($categoryId, fn ($q, $id) => $q->where('service_category_id', $id))
            ->groupBy('service_area_id', 'service_category_id')
            ->get()
            ->keyBy(fn ($row) => "{$row->service_area_id}-{$row->service_category_id}")
            ->map(fn ($row) => (int) $row->providers_count);
    }

    private function classify(int $requestsCount, int $providersCount): string
    {
        if ($providersCount === 0) {
            return $requestsCount > 0 ? 'critical_shortage' : 'no_activity';
        }

        $ratio = $requestsCount / $providersCount;

        return match (true) {
            $ratio >= self::SHORTAGE_RATIO_THRESHOLD => 'shortage',
            $ratio < self::SURPLUS_RATIO_THRESHOLD => 'surplus',
            default => 'balanced',
        };
    }
}
