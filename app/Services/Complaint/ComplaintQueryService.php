<?php

namespace App\Services\Complaint;

use App\Filters\ComplaintFilter;
use App\Models\Complaint;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ComplaintQueryService
{
    public function getAll(array $filters = []): LengthAwarePaginator
    {
        $query = Complaint::query()->with([
            'user:id,first_name,last_name',
            'againstUser:id,first_name,last_name',
        ]);

        (new ComplaintFilter($filters))->apply($query);

        return $query->latest()->paginate($filters['per_page'] ?? 15);
    }
}
