<?php

namespace App\Filters;

class ComplaintFilter extends QueryFilter
{
    public function allowedFilters(): array
    {
        return [
            'status',
            'reason',
            'service_request_id',
            'user_id',
            'against_user_id',
            'date_from',
            'date_to',
        ];
    }

    protected function status(string $value): void
    {
        $this->builder->where('status', $value);
    }

    protected function reason(string $value): void
    {
        $this->builder->where('reason', $value);
    }

    protected function serviceRequestId(int $value): void
    {
        $this->builder->where('service_request_id', $value);
    }

    protected function userId(int $value): void
    {
        $this->builder->where('user_id', $value);
    }

    protected function againstUserId(int $value): void
    {
        $this->builder->where('against_user_id', $value);
    }

    protected function dateFrom(string $value): void
    {
        $this->builder->whereDate('created_at', '>=', $value);
    }

    protected function dateTo(string $value): void
    {
        $this->builder->whereDate('created_at', '<=', $value);
    }
}
