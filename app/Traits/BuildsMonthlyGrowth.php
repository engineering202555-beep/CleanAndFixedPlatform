<?php

namespace App\Traits;


use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
trait BuildsMonthlyGrowth
{
    protected function resolvePeriod(array $filters): array
    {
        if (! empty($filters['date_from']) && ! empty($filters['date_to'])) {
            return [
                Carbon::parse($filters['date_from'])->startOfDay(),
                Carbon::parse($filters['date_to'])->endOfDay(),
            ];
        }

        $year = $filters['year'] ?? now()->year;

        return [
            Carbon::create((int) $year, 1, 1)->startOfDay(),
            Carbon::create((int) $year, 12, 31)->endOfDay(),
        ];
    }

    protected function fillMonthlyGaps(Collection $results, Carbon $start, Carbon $end): Collection
    {
        $period = CarbonPeriod::create($start->copy()->startOfMonth(), '1 month', $end->copy()->startOfMonth());
        $keyed = $results->keyBy('month');

        return collect($period)->map(function (Carbon $date) use ($keyed) {
            $key = $date->format('Y-m');

            return [
                'month' => $key,
                'count' => (int) ($keyed[$key]->count ?? 0),
            ];
        })->values();
    }

    /**
     * فترة انتهت بالكامل بالماضي = بيانات ثابتة، Cache طويل (يوم كامل).
     * فترة فيها الشهر الحالي = بيانات لسا بتتغيّر، Cache قصير (دقايق).
     */
    protected function rememberStats(string $cacheKey, Carbon $periodEnd, callable $callback): Collection
    {
        $ttl = $periodEnd->isPast() ? now()->addDay() : now()->addMinutes(10);

        return Cache::remember($cacheKey, $ttl, $callback);
    }
}
