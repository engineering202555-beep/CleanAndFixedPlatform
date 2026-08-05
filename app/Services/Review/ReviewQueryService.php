<?php

namespace App\Services\Review;

use App\Filters\ReviewFilter;
use App\Models\Review;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ReviewQueryService
{
    /**
     * Eager loading كامل لكل العلاقات المطلوبة بضربة استعلام وحدة
     * لكل نوع علاقة (منع N+1 بالكامل):
     * customer.user, serviceProvider.user, serviceProvider.profileImage,
     * serviceRequest.serviceCategory.
     */
    public function getAll(array $filters = []): LengthAwarePaginator
    {
        $query = Review::query()->with([
            'customer.user:id,first_name,last_name',
            'serviceProvider.user:id,first_name,last_name',
            'serviceProvider.profileImage',
            'serviceRequest.serviceCategory:id,name',
        ]);

        (new ReviewFilter($filters))->apply($query);

        return $query->paginate($filters['per_page'] ?? 15);
    }
}
