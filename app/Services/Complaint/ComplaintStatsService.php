<?php

namespace App\Services\Complaint;

use App\Models\Complaint;
use App\Models\ServiceArea;
use App\Models\ServiceRequest;
use App\Services\ServiceProvider\ServiceProviderService;
use Illuminate\Support\Collection;

class ComplaintStatsService
{
    public function __construct(
        private readonly ServiceProviderService $providerComplaintStats
    ) {
    }

    public function getStats(array $filters = []): array
    {
        return [
            'overview'          => $this->buildOverview($filters),
            'reasons_breakdown' => $this->buildReasonsBreakdown($filters),
            'top_areas'         => $this->buildTopAreas($filters),
            // إعادة استخدام مباشرة للـ Service الموجود أصلاً، بدل
            // ما نكرر نفس منطق استثناء الشكاوى المرفوضة من جديد.
            'top_providers'     => $this->providerComplaintStats->getMostComplainedAgainst(5),
        ];
    }

    private function buildOverview(array $filters): array
    {
        $base = Complaint::query()
            ->when($filters['date_from'] ?? null, fn ($q, $d) => $q->whereDate('created_at', '>=', $d))
            ->when($filters['date_to'] ?? null, fn ($q, $d) => $q->whereDate('created_at', '<=', $d));

        return [
            'total'     => (clone $base)->count(),
            'pending'   => (clone $base)->where('status', 'pending')->count(),
            'in_review' => (clone $base)->where('status', 'in_review')->count(),
            'resolved'  => (clone $base)->where('status', 'resolved')->count(),
            'rejected'  => (clone $base)->where('status', 'rejected')->count(),
        ];
    }

    private function buildReasonsBreakdown(array $filters): Collection
    {
        $rows = Complaint::query()
            ->select('reason')
            ->selectRaw('count(*) as count')
            ->when($filters['date_from'] ?? null, fn ($q, $d) => $q->whereDate('created_at', '>=', $d))
            ->when($filters['date_to'] ?? null, fn ($q, $d) => $q->whereDate('created_at', '<=', $d))
            ->groupBy('reason')
            ->orderByDesc('count')
            ->get();

        $total = $rows->sum('count');

        return $rows->map(function ($row) use ($total) {
            return [
                'reason'     => $row->reason,
                'count'      => (int) $row->count,
                'percentage' => $total > 0 ? round(($row->count / $total) * 100, 2) : 0,
            ];
        })->values();
    }

    /**
     * complaint_rate بيقارن الشكاوى بعدد الطلبات الفعلي بنفس المنطقة،
     * مش عدد خام لحاله (منطقة فيها 1000 طلب طبيعي شكاواها أكتر من
     * منطقة فيها 50). العلاقة complaint → service_request →
     * service_area (مش complaint مباشرة)، بالضبط متل ما نبّهتيني.
     * 3 استعلامات مجمّعة بس (بلا Loop)، بغض النظر عن عدد المناطق.
     */
    private function buildTopAreas(array $filters): Collection
    {
        $complaintCounts = Complaint::query()
            ->join('service_requests', 'service_requests.id', '=', 'complaints.service_request_id')
            ->select('service_requests.service_area_id')
            ->selectRaw('count(*) as complaints_count')
            ->when($filters['date_from'] ?? null, fn ($q, $d) => $q->whereDate('complaints.created_at', '>=', $d))
            ->when($filters['date_to'] ?? null, fn ($q, $d) => $q->whereDate('complaints.created_at', '<=', $d))
            ->groupBy('service_requests.service_area_id')
            ->get()
            ->keyBy('service_area_id');

        if ($complaintCounts->isEmpty()) {
            return collect();
        }

        $requestCounts = ServiceRequest::query()
            ->select('service_area_id')
            ->selectRaw('count(*) as requests_count')
            ->whereIn('service_area_id', $complaintCounts->keys())
            ->groupBy('service_area_id')
            ->get()
            ->keyBy('service_area_id');

        $areas = ServiceArea::query()
            ->whereIn('id', $complaintCounts->keys())
            ->get(['id', 'area_name', 'city'])
            ->keyBy('id');

        return $complaintCounts->map(function ($row, $areaId) use ($requestCounts, $areas) {
            $area = $areas->get($areaId);
            $requestsCount = (int) ($requestCounts->get($areaId)->requests_count ?? 0);
            $complaintsCount = (int) $row->complaints_count;

            return [
                'area_id'          => $areaId,
                'area_name'        => $area?->area_name,
                'city'             => $area?->city,
                'complaints_count' => $complaintsCount,
                'requests_count'   => $requestsCount,
                'complaint_rate'   => $requestsCount > 0 ? round(($complaintsCount / $requestsCount) * 100, 2) : null,
            ];
        })->sortByDesc('complaints_count')->values();
    }
}

