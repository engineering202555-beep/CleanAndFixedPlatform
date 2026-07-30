<?php

namespace App\Filters;

use Illuminate\Database\Eloquent\Builder;

class ServiceProviderFilter extends QueryFilter
{
    /**
     * القائمة البيضاء الوحيدة للأعمدة المسموح الترتيب فيها.
     * أي قيمة تانية بترجع تلقائياً للـ fallback (rating).
     */
    private const SORTABLE_COLUMNS = ['rating', 'experience_years', 'inspection_price', 'created_at'];

    private const SORT_DIRECTIONS = ['asc', 'desc'];

    public function allowedFilters(): array
    {
        return [
            'category_id',
            'area_id',
            'subscription_id',
            'search',
            'sort_by',
            'sort_direction',
        ];
    }

    protected function defaults(): array
    {
        return [
            'sort_by' => 'rating',
            'sort_direction' => 'desc',
        ];
    }

    protected function categoryId(int $value): void
    {
        $this->builder->where('service_category_id', $value);
    }

    protected function areaId(int $value): void
    {
        $this->builder->where('service_area_id', $value);
    }

    /**
     * فلترة حسب خطة الاشتراك الحالية النشطة لمقدم الخدمة.
     */
    protected function subscriptionId(int $value): void
    {
        $this->builder->whereHas('subscriptions', function (Builder $query) use ($value) {
            $query->where('subscription_id', $value)
                ->where('status', 'active');
        });
    }

    protected function search(string $value): void
    {
        $this->builder->whereHas('user', function (Builder $query) use ($value) {
            $query->where('first_name', 'like', "%{$value}%")
                ->orWhere('last_name', 'like', "%{$value}%");
        });
    }

    /**
     * sort_direction ما إلها method خاصة فيها (ما بتتطبق لحالها على
     * الاستعلام)، بتُقرأ هون مباشرة من $this->resolved وقت تطبيق الترتيب.
     */
    protected function sortBy(string $value): void
    {
        $column = in_array($value, self::SORTABLE_COLUMNS, true) ? $value : 'rating';

        $direction = in_array($this->resolved['sort_direction'] ?? null, self::SORT_DIRECTIONS, true)
            ? $this->resolved['sort_direction']
            : 'desc';

        $this->builder->orderBy($column, $direction);
    }
}
