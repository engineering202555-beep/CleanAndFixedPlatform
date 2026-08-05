<?php

namespace App\Filters;

use Illuminate\Database\Eloquent\Builder;

class ReviewFilter extends QueryFilter
{
    public function allowedFilters(): array
    {
        return [
            'customer_search',
            'provider_search',
            'category_id',
            'rating',
            'sort_by',
        ];
    }

    protected function defaults(): array
    {
        return [
            'sort_by' => 'latest',
        ];
    }

    protected function customerSearch(string $value): void
    {
        $this->builder->whereHas('customer.user', function (Builder $query) use ($value) {
            $query->where('first_name', 'like', "%{$value}%")
                ->orWhere('last_name', 'like', "%{$value}%");
        });
    }

    protected function providerSearch(string $value): void
    {
        $this->builder->whereHas('serviceProvider.user', function (Builder $query) use ($value) {
            $query->where('first_name', 'like', "%{$value}%")
                ->orWhere('last_name', 'like', "%{$value}%");
        });
    }

    protected function categoryId(int $value): void
    {
        $this->builder->whereHas('serviceRequest', function (Builder $query) use ($value) {
            $query->where('service_category_id', $value);
        });
    }

    protected function rating(int $value): void
    {
        $this->builder->where('provider_rating', $value);
    }

    protected function sortBy(string $value): void
    {
        match ($value) {
            'highest_rated' => $this->builder->orderByDesc('provider_rating')->orderByDesc('created_at'),
            default => $this->builder->orderByDesc('created_at'),
        };
    }
}
