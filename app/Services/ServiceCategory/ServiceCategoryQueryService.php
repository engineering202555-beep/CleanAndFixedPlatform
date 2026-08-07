<?php

namespace App\Services\ServiceCategory;

use App\Filters\ServiceCategoryFilter;
use App\Models\ServiceCategory;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ServiceCategoryQueryService
{
    /**
     * withCount بيحسب العددين (مقدمي خدمة + طلبات) باستعلامين
     * فرعيين (Subquery) ضمن نفس استعلام SQL الرئيسي — لا استعلام
     * إضافي لكل صف (N+1 ممنوع تماماً هون).
     */
    public function getAll(array $filters = []): LengthAwarePaginator
    {
        $query = ServiceCategory::query()->withCount([
            'serviceProviders as providers_count',
            'serviceRequests as requests_count',
        ]);

        (new ServiceCategoryFilter($filters))->apply($query);

        return $query->paginate($filters['per_page'] ?? 15);
    }
}
