<?php

namespace App\Services\Stats;

use App\Models\Offer;
use App\Models\ServiceArea;
use App\Models\ServiceCategory;
use Illuminate\Support\Collection;

class PriceIntelligenceService
{
    // عتبة "منطقة غالية/رخيصة جداً" — 20% فوق/تحت المتوسط العام الموزون
    private const HIGH_PRICE_THRESHOLD_RATIO = 1.2;
    private const LOW_PRICE_THRESHOLD_RATIO = 0.8;

    /**
     * مبنية بالكامل على العروض المقبولة (accepted) وسعرها الحقيقي
     * المتفق عليه، مش أي عرض مُقترح لم يُقبل. النتيجة مجمّعة دايماً
     * بمستوى (منطقة × تصنيف) — هيك نفس الـ Endpoint يصلح لعرض
     * "مقارنة مناطق لتصنيف واحد" أو "مقارنة تصنيفات بمنطقة واحدة"،
     * حسب شو الفلتر يلي الفرونت إند حدده، بدون أي تعديل Backend.
     */
    public function compare(array $filters = []): array
    {
        $offers = $this->fetchAcceptedOffers($filters);

        $grouped = $offers->groupBy(function ($offer) {
            return $offer->serviceRequest->service_area_id.'-'.$offer->serviceRequest->service_category_id;
        });

        $areas = ServiceArea::query()->get(['id', 'area_name', 'city'])->keyBy('id');
        $categories = ServiceCategory::query()->get(['id', 'name'])->keyBy('id');

        $rows = $grouped->map(function ($group) use ($areas, $categories) {
            $first = $group->first()->serviceRequest;
            $area = $areas->get($first->service_area_id);
            $category = $categories->get($first->service_category_id);

            $prices = $group->pluck('price')->map(fn ($p) => (float) $p)->sort()->values();

            return [
                'area_id'              => $area?->id,
                'area_name'            => $area?->area_name,
                'city'                 => $area?->city,
                'category_id'          => $category?->id,
                'category_name'        => $category?->name,
                'average_price'        => round($prices->avg(), 2),
                'min_price'            => (float) $prices->min(),
                'max_price'            => (float) $prices->max(),
                'median_price'         => $this->median($prices),
                'requests_count'       => $group->pluck('service_request_id')->unique()->count(),
                'accepted_offers_count' => $group->count(),
            ];
        })->values();

        return [
            'rows'     => $rows,
            'insights' => $this->buildInsights($rows, $offers),
        ];
    }

    /**
     * متابعة متوسط السعر شهرياً — لازم فلتر تضييق واحد على الأقل
     * (منطقة أو تصنيف) مفروض أصلاً من الـ Request، وإلا بيرجع خطوط
     * كتيرة متراكبة بلا معنى بمخطط واحد.
     */
    public function monthlyTrend(array $filters = []): Collection
    {
        return Offer::query()
            ->selectRaw("DATE_FORMAT(service_requests.created_at, '%Y-%m') as month")
            ->selectRaw('ROUND(AVG(offers.price), 2) as average_price')
            ->selectRaw('COUNT(*) as accepted_offers_count')
            ->join('service_requests', 'service_requests.id', '=', 'offers.service_request_id')
            ->where('offers.status', 'accepted')
            ->whereNotNull('offers.price')
            ->when($filters['service_category_id'] ?? null, function ($query, $id) {
                $query->where('service_requests.service_category_id', $id);
            })
            ->when($filters['area_id'] ?? null, function ($query, $id) {
                $query->where('service_requests.service_area_id', $id);
            })
            ->when($filters['date_from'] ?? null, function ($query, $date) {
                $query->whereDate('service_requests.created_at', '>=', $date);
            })
            ->when($filters['date_to'] ?? null, function ($query, $date) {
                $query->whereDate('service_requests.created_at', '<=', $date);
            })
            ->groupBy('month')
            ->orderBy('month')
            ->get();
    }

    private function fetchAcceptedOffers(array $filters): Collection
    {
        return Offer::query()
            ->where('status', 'accepted')
            ->whereNotNull('price')
            ->with(['serviceRequest:id,service_area_id,service_category_id,request_type,created_at'])
            ->whereHas('serviceRequest', function ($query) use ($filters) {
                $query
                    ->when($filters['service_category_id'] ?? null, function ($q, $id) {
                        $q->where('service_category_id', $id);
                    })
                    ->when($filters['category_ids'] ?? null, function ($q, $ids) {
                        $q->whereIn('service_category_id', $ids);
                    })
                    ->when($filters['area_id'] ?? null, function ($q, $id) {
                        $q->where('service_area_id', $id);
                    })
                    ->when($filters['area_ids'] ?? null, function ($q, $ids) {
                        $q->whereIn('service_area_id', $ids);
                    })
                    ->when($filters['request_type'] ?? null, function ($q, $type) {
                        $q->where('request_type', $type);
                    })
                    ->when($filters['date_from'] ?? null, function ($q, $date) {
                        $q->whereDate('created_at', '>=', $date);
                    })
                    ->when($filters['date_to'] ?? null, function ($q, $date) {
                        $q->whereDate('created_at', '<=', $date);
                    });
            })
            ->get();
    }

    private function median(Collection $sortedPrices): float
    {
        $count = $sortedPrices->count();

        if ($count === 0) {
            return 0.0;
        }

        $middle = intdiv($count, 2);

        if ($count % 2 === 0) {
            return round(($sortedPrices[$middle - 1] + $sortedPrices[$middle]) / 2, 2);
        }

        return round($sortedPrices[$middle], 2);
    }

    /**
     * المتوسط العام هون "موزون" (كل عرض مقبول بيدخل بالحساب لحاله)،
     * مش "متوسط المتوسطات" — أدق إحصائياً لو أعداد العروض متفاوتة
     * بشكل كبير بين المناطق.
     */
    private function buildInsights(Collection $rows, Collection $allOffers): array
    {
        if ($rows->isEmpty()) {
            return [
                'overall_weighted_average' => 0,
                'most_expensive'           => null,
                'cheapest'                 => null,
                'price_variance_percentage' => 0,
                'ranking'                  => [],
                'high_price_rows'          => [],
                'low_price_rows'           => [],
            ];
        }

        $overallAverage = round($allOffers->avg(fn ($o) => (float) $o->price), 2);

        $ranked = $rows->sortByDesc('average_price')->values();
        $mostExpensive = $ranked->first();
        $cheapest = $ranked->last();

        $variancePercentage = $cheapest['average_price'] > 0
            ? round((($mostExpensive['average_price'] - $cheapest['average_price']) / $cheapest['average_price']) * 100, 2)
            : 0;

        $highPriceRows = $rows->filter(fn ($row) => $row['average_price'] > $overallAverage * self::HIGH_PRICE_THRESHOLD_RATIO)->values();
        $lowPriceRows = $rows->filter(fn ($row) => $row['average_price'] < $overallAverage * self::LOW_PRICE_THRESHOLD_RATIO)->values();

        return [
            'overall_weighted_average'  => $overallAverage,
            'most_expensive'            => $mostExpensive,
            'cheapest'                  => $cheapest,
            'price_variance_percentage' => $variancePercentage,
            'ranking'                   => $ranked->map(fn ($row, $i) => array_merge($row, ['rank' => $i + 1]))->values(),
            'high_price_rows'           => $highPriceRows,
            'low_price_rows'            => $lowPriceRows,
        ];
    }
}
