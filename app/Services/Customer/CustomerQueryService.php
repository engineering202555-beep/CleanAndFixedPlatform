<?php

namespace App\Services\Customer;

use App\Models\Customer;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class CustomerQueryService
{
    /**
     * فقط الزبائن يلي تحقق رقم هاتفهم فعلياً (users.phone_verified_at
     * IS NOT NULL) — هذا المصدر الحقيقي، مش جدول phone_otps التاريخي.
     * لا latitude/longitude ولا address_text محمّلين هون أبداً — هاي
     * بيانات حساسة ما إلها داعي بقائمة عامة (خصوصية).
     */
    public function getVerifiedCustomers(array $filters = []): LengthAwarePaginator
    {
        return Customer::query()
            ->whereHas('user', function ($query) {
                $query->whereNotNull('phone_verified_at');
            })
            ->with([
                'user:id,first_name,last_name,phone_number',
                'serviceArea:id,city,area_name',
            ])
            ->withCount([
                'serviceRequests as completed_requests_count' => function ($query) {
                    $query->where('status', 'completed');
                },
            ])
            ->when($filters['area_id'] ?? null, function ($query, $areaId) {
                $query->where('service_area_id', $areaId);
            })
            ->when($filters['joined_from'] ?? null, function ($query, $date) {
                $query->whereDate('created_at', '>=', $date);
            })
            ->when($filters['joined_to'] ?? null, function ($query, $date) {
                $query->whereDate('created_at', '<=', $date);
            })
            ->latest()
            ->paginate( 15);
    }

    public function getBlockedCustomers(int $perPage = 15): LengthAwarePaginator
    {
        return Customer::query()
            ->where('status', 'blocked')
            ->with([
                'user:id,first_name,last_name,phone_number',
                'serviceArea:id,city,area_name',
            ])
            ->orderBy('blocked_until')
            ->paginate($perPage);
    }
}
