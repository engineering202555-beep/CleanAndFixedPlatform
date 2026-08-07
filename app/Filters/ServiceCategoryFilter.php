<?php

namespace App\Filters;

class ServiceCategoryFilter extends QueryFilter
{
    private const SORTABLE_COLUMNS = ['name', 'providers_count', 'requests_count', 'created_at'];
    private const SORT_DIRECTIONS = ['asc', 'desc'];

    public function allowedFilters(): array
    {
        return ['search', 'sort_by', 'sort_direction'];
    }

    protected function defaults(): array
    {
        return [
            'sort_by' => 'created_at',
            'sort_direction' => 'desc',
        ];
    }

    protected function search(string $value): void
    {
        $this->builder->where('name', 'like', "%{$value}%");
    }

    protected function sortBy(string $value): void
    {
        $column = in_array($value, self::SORTABLE_COLUMNS, true) ? $value : 'created_at';

        $direction = in_array($this->resolved['sort_direction'] ?? null, self::SORT_DIRECTIONS, true)
            ? $this->resolved['sort_direction']
            : 'desc';

        $this->builder->orderBy($column, $direction);
    }
}
