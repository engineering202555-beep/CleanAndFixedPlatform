<?php

namespace App\Services\Stats;

use App\Models\SubscriptionProvider;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class SubscriptionRevenueStatsService
{
    /**
     * نقطة الدخول الوحيدة: بترجع كل شي جاهز للوحة تحكم واحدة
     * (Overview + Line Chart شهري + Bar Chart حسب الخطة) بضربة
     * واحدة، بدل 3 endpoints منفصلة.
     */
    public function getStats(array $filters = []): array
    {
        $revenueByPlan = $this->buildRevenueByPlan($filters);
        $monthlyTrend = $this->buildMonthlyTrend($filters);
        $overview = $this->buildOverview($filters, $revenueByPlan, $monthlyTrend);

        return [
            'overview'        => $overview,
            'monthly_trend'   => $monthlyTrend,
            'revenue_by_plan' => $revenueByPlan,
        ];
    }

    /**
     * الاستعلام الأساسي الوحيد لتعريف "إيراد فعلي" — معتمد بكل مكان
     * تاني بهذا الـ Service، بدل ما يتكرر الشرط بأكثر من مكان.
     * price_paid IS NOT NULL + > 0 + is_complimentary=false يغطي
     * كل الحالات الستة يلي حللناها فوق، بغض النظر عن status الحالي.
     */
    private function revenueEligibleQuery(array $filters): Builder
    {
        return SubscriptionProvider::query()
            ->whereNotNull('price_paid')
            ->where('price_paid', '>', 0)
            ->where('is_complimentary', false)
            ->when($filters['subscription_id'] ?? null, function ($query, $id) {
                $query->where('subscription_id', $id);
            })
            ->when($filters['status'] ?? null, function ($query, $status) {
                $query->where('status', $status);
            })
            ->when($this->resolvePeriod($filters), function ($query, $period) {
                $query->whereBetween('starts_at', $period);
            });
    }

    /**
     * null = بلا قيد زمني (كل التاريخ)، وهذا الافتراضي المتعمّد هون
     * (بعكس إحصائيات النمو السابقة يلي افتراضها السنة الحالية) —
     * تقرير إيرادات عادةً بده يشوف الصورة الكاملة أول ما يفتحها.
     */
    private function resolvePeriod(array $filters): ?array
    {
        if (! empty($filters['date_from']) && ! empty($filters['date_to'])) {
            return [
                Carbon::parse($filters['date_from'])->startOfDay(),
                Carbon::parse($filters['date_to'])->endOfDay(),
            ];
        }

        if (! empty($filters['year'])) {
            return [
                Carbon::create((int) $filters['year'], 1, 1)->startOfDay(),
                Carbon::create((int) $filters['year'], 12, 31)->endOfDay(),
            ];
        }

        return null;
    }

    private function buildOverview(array $filters, Collection $revenueByPlan, Collection $monthlyTrend): array
    {
        $eligible = $this->revenueEligibleQuery($filters);

        $totalRevenue = (clone $eligible)->sum('price_paid');
        $paidCount = (clone $eligible)->count();
        $activeCount = (clone $eligible)->where('status', 'active')->count();
        $cancelledCount = (clone $eligible)->where('status', 'cancelled')->count();

        // pending_payment أصلاً مافيها price_paid، فبتتفلتر بمنطق
        // مختلف تماماً (created_at، مش starts_at) — هاي طلبات لسا
        // ما دفعت، مش جزء من "الإيراد" بأي تعريف.
        $pendingCount = SubscriptionProvider::query()
            ->where('status', 'pending_payment')
            ->when($filters['subscription_id'] ?? null, fn ($q, $id) => $q->where('subscription_id', $id))
            ->when($this->resolvePeriod($filters), function ($q, $period) {
                $q->whereBetween('created_at', $period);
            })
            ->count();

        return [
            'total_revenue'                      => round($totalRevenue, 2),
            'paid_subscriptions_count'            => $paidCount,
            'active_paid_subscriptions_count'     => $activeCount,
            'cancelled_paid_subscriptions_count'  => $cancelledCount,
            'pending_payment_count'               => $pendingCount,
            'average_subscription_revenue'        => $paidCount > 0 ? round($totalRevenue / $paidCount, 2) : 0,
            'top_plan_by_revenue'                 => $revenueByPlan->sortByDesc('revenue')->first(),
            'top_month_by_revenue'                => $monthlyTrend->sortByDesc('revenue')->first(),
        ];
    }

    /**
     * Line Chart: الشهر → الإيراد. لاحظي التجميع على starts_at
     * (لحظة التفعيل الفعلي/الدفع)، مش created_at (لحظة اختيار
     * الخطة قبل الدفع، ممكن تكون بشهر مختلف تماماً).
     */
    private function buildMonthlyTrend(array $filters): Collection
    {
        $rows = $this->revenueEligibleQuery($filters)
            ->selectRaw("DATE_FORMAT(starts_at, '%Y-%m') as month")
            ->selectRaw('SUM(price_paid) as revenue')
            ->selectRaw('COUNT(*) as paid_subscriptions_count')
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        return $rows->map(function ($row) {
            return [
                'month'                     => $row->month,
                'revenue'                   => round((float) $row->revenue, 2),
                'paid_subscriptions_count'  => (int) $row->paid_subscriptions_count,
            ];
        });
    }

    /**
     * Bar Chart: مقارنة الخطط حسب الإيراد الفعلي، مش عدد المشتركين.
     * الأسماء والأنواع مجلوبة بضربة استعلام وحدة (join ضمني عبر
     * subscription_id)، بدون N+1.
     */
    private function buildRevenueByPlan(array $filters): Collection
    {
        $rows = $this->revenueEligibleQuery($filters)
            ->join('subscriptions', 'subscriptions.id', '=', 'subscription_providers.subscription_id')
            ->selectRaw('subscriptions.id as subscription_id')
            ->selectRaw('subscriptions.type as plan_type')
            ->selectRaw('SUM(subscription_providers.price_paid) as revenue')
            ->selectRaw('COUNT(*) as subscribers_count')
            ->groupBy('subscriptions.id', 'subscriptions.type')
            ->orderByDesc('revenue')
            ->get();

        $totalRevenue = $rows->sum('revenue');

        return $rows->map(function ($row) use ($totalRevenue) {
            return [
                'subscription_id'   => $row->subscription_id,
                'plan_type'         => $row->plan_type,
                'revenue'           => round((float) $row->revenue, 2),
                'subscribers_count' => (int) $row->subscribers_count,
                'percentage'        => $totalRevenue > 0 ? round(($row->revenue / $totalRevenue) * 100, 2) : 0,
            ];
        })->values();
    }
}
