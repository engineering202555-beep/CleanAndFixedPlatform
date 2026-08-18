<?php

namespace App\Services\ServiceRequest;

use App\Models\ServiceProvider;
use App\Models\ServiceRequest;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class ProviderRequestQueryService
{
    private const SEARCHING_STATUSES = ['pending_local', 'pending_global'];

    public function __construct(
        private readonly ProviderEligibilityService $eligibilityService
    ) {
    }

    public function getEligibleRequests(ServiceProvider $provider, array $filters = []): LengthAwarePaginator
    {
        if ($provider->account_status !== 'active') {
            return ServiceRequest::query()->whereRaw('1 = 0')->paginate($filters['per_page'] ?? 15);
        }

        $eligibleIds = ServiceRequest::query()
            ->where('service_category_id', $provider->service_category_id)
            ->whereIn('status', self::SEARCHING_STATUSES)
            ->with('serviceArea:id,city')
            ->get()
            ->filter(fn (ServiceRequest $request) => $this->eligibilityService->isEligible($provider, $request))
            ->pluck('id');

        return ServiceRequest::query()
            ->whereIn('id', $eligibleIds)
            ->with([
                'serviceCategory:id,name',
                'serviceArea:id,area_name,city',
                'customer.user:id,first_name,last_name',
            ])
            ->withExists([
                'offers as has_my_offer' => function ($q) use ($provider) {
                    $q->where('service_provider_id', $provider->id);
                },
            ])
            ->orderByDesc('is_urgent')
            ->latest()
            ->paginate($filters['per_page'] ?? 15);
    }

    /**
     * كل الطلبات يلي مقدم الخدمة قدّم عرض عليها ولو مرة، بغض النظر
     * عن حالتها الحالية — بعكس getEligibleRequests() المقصورة على
     * pending_* بس. whereHas('offers') هي شرط الملكية/العلاقة
     * الوحيد، والفلاتر التلاتة (status/request_type/is_urgent)
     * تضييق إضافي فوقها، مش بديل عنها.
     */
    public function getMyRequests(ServiceProvider $provider, array $filters = []): LengthAwarePaginator
    {
        return ServiceRequest::query()
            ->whereHas('offers', function ($query) use ($provider) {
                $query->where('service_provider_id', $provider->id);
            })
            ->when($filters['status'] ?? null, function ($query, $status) {
                $query->where('status', $status);
            })
            ->when($filters['request_type'] ?? null, function ($query, $type) {
                $query->where('request_type', $type);
            })
            ->when(array_key_exists('is_urgent', $filters) && $filters['is_urgent'] !== null, function ($query) use ($filters) {
                $query->where('is_urgent', (bool) $filters['is_urgent']);
            })
            ->with([
                'serviceCategory:id,name',
                'serviceArea:id,area_name,city',
                'customer.user:id,first_name,last_name',
            ])
            ->withExists([
                'offers as has_my_offer' => function ($q) use ($provider) {
                    $q->where('service_provider_id', $provider->id);
                },
            ])
            ->orderByDesc('is_urgent')
            ->latest()
            ->paginate($filters['per_page'] ?? 15);
    }

    public function getRequestDetails(ServiceProvider $provider, ServiceRequest $request): ServiceRequest
    {
        $hasExistingOffer = $request->offers()->where('service_provider_id', $provider->id)->exists();

        if (! $hasExistingOffer && ! $this->eligibilityService->isEligible($provider, $request)) {
            throw new AccessDeniedHttpException('هذا الطلب غير متاح لك.');
        }

        return $request->load([
            'serviceCategory:id,name',
            'serviceArea:id,area_name,city',
            'customer.user:id,first_name,last_name',
            'images',
        ]);
    }
}
