<?php

namespace App\Services\Customer;

use App\Models\BlockedServiceProvider;
use App\Models\ServiceRequest;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

class BlockedProviderQueryService
{
    /**
     * "هل يوجد طلبات سابقة بينهما" محسوبة بـ Correlated Subqueries
     * جوّا نفس استعلام SQL الرئيسي (addSelect)، مش بـ Loop بعد الجلب.
     * لو حسبناها بـ Loop، كل صف كان رح يحتاج استعلام إضافي بذاته
     * (N+1 كلاسيكي)، وهذا بالضبط الشي يلي لازم نتفاداه.
     */
    public function getAll(array $filters = []): LengthAwarePaginator
    {
        $query = BlockedServiceProvider::query()
            ->with([
                'customer.user:id,first_name,last_name',
                'serviceProvider.user:id,first_name,last_name',
                'serviceProvider.profileImage',
            ])
            ->addSelect([
                'interactions_count'   => $this->interactionsCountSubquery(),
                'last_request_id'      => $this->lastRequestSubquery('service_requests.id'),
                'last_request_status'  => $this->lastRequestSubquery('service_requests.status'),
                'last_request_date'    => $this->lastRequestSubquery('service_requests.created_at'),
            ])
            ->when($filters['customer_id'] ?? null, function (Builder $query, $customerId) {
                $query->where('customer_id', $customerId);
            })
            ->latest('created_at');

        return $query->paginate($filters['per_page'] ?? 15);
    }

    private function interactionsCountSubquery()
    {
        return ServiceRequest::query()
            ->selectRaw('count(distinct service_requests.id)')
            ->join('offers', 'offers.service_request_id', '=', 'service_requests.id')
            ->whereColumn('service_requests.customer_id', 'blocked_service_providers.customer_id')
            ->whereColumn('offers.service_provider_id', 'blocked_service_providers.service_provider_id');
    }

    private function lastRequestSubquery(string $column)
    {
        return ServiceRequest::query()
            ->select($column)
            ->join('offers', 'offers.service_request_id', '=', 'service_requests.id')
            ->whereColumn('service_requests.customer_id', 'blocked_service_providers.customer_id')
            ->whereColumn('offers.service_provider_id', 'blocked_service_providers.service_provider_id')
            ->orderByDesc('service_requests.created_at')
            ->limit(1);
    }
}
