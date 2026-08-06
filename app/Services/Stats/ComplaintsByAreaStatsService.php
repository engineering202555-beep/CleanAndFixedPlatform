<?php

namespace App\Services\Stats;

use App\Models\Complaint;
use App\Models\ServiceArea;
use Illuminate\Support\Collection;

class ComplaintsByAreaStatsService
{
    /**
     * الشكاوى العامة (service_request_id = null) مُستثناة تماماً هون
     * لأنه أصلاً مافيها منطقة نربطها بيها منطقياً. العدّ هون لعدد
     * صفوف الشكاوى الفعلية (Complaint)، مش عدد الطلبات "يلي عليها
     * شكوى" — طلب واحد ممكن يكون عليه أكتر من شكوى.
     */
    public function getStats(array $filters = []): Collection
    {
        $areas = ServiceArea::query()
            ->addSelect([
                'complaints_count' => Complaint::query()
                    ->selectRaw('count(*)')
                    ->join('service_requests', 'service_requests.id', '=', 'complaints.service_request_id')
                    ->whereColumn('service_requests.service_area_id', 'service_areas.id')
                    ->when($filters['date_from'] ?? null, function ($query, $date) {
                        $query->whereDate('complaints.created_at', '>=', $date);
                    })
                    ->when($filters['date_to'] ?? null, function ($query, $date) {
                        $query->whereDate('complaints.created_at', '<=', $date);
                    }),
            ])
            ->having('complaints_count', '>', 0)
            ->orderByDesc('complaints_count')
            ->get();

        $totalComplaints = $areas->sum('complaints_count');

        return $areas->values()->map(function ($area, $index) use ($totalComplaints) {
            return [
                'rank'             => $index + 1,
                'area_id'          => $area->id,
                'area_name'        => $area->area_name,
                'city'             => $area->city,
                'complaints_count' => (int) $area->complaints_count,
                'percentage'       => $totalComplaints > 0
                    ? round(($area->complaints_count / $totalComplaints) * 100, 2)
                    : 0,
            ];
        });
    }
}
