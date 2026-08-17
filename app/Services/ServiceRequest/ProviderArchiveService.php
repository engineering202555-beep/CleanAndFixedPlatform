<?php

namespace App\Services\ServiceRequest;

use App\Models\ServiceProvider;
use App\Models\ServiceRequest;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ProviderArchiveService
{
    /**
     * whereHas بدل عمود مباشر (ServiceRequest ما فيها service_provider_id
     * موثوق، نفس القرار المعتمد بكل مكان تاني) — استعلام واحد
     * (join ضمني)، بدون N+1، مع تحميل التقييم مسبقاً (Eager Load).
     */
    public function getCompletedRequests(ServiceProvider $provider, int $perPage = 15): LengthAwarePaginator
    {
        return ServiceRequest::query()
            ->where('status', 'completed')
            ->whereHas('offers', function ($query) use ($provider) {
                $query->where('service_provider_id', $provider->id)
                    ->where('status', 'accepted');
            })
            ->with([
                'serviceCategory:id,name',
                'serviceArea:id,area_name,city',
                'customer.user:id,first_name,last_name',
                'review',
            ])
            ->latest()
            ->paginate($perPage);
    }
}
