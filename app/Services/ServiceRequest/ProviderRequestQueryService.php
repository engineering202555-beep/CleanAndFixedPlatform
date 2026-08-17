<?php

namespace App\Services\ServiceRequest;

use App\Models\ServiceProvider;
use App\Models\ServiceRequest;
use App\Traits\ChecksProviderAvailability;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class ProviderRequestQueryService
{
    private const SEARCHING_STATUSES = ['pending_local', 'pending_global'];

    public function __construct(
        private readonly ProviderEligibilityService $eligibilityService
    ) {
    }

    /**
     * صفر تكرار لمنطق الأهلية هون — الفلترة الوحيدة بمستوى SQL هي
     * "نفس التصنيف + بمرحلة بحث فعلاً" (لتقليل حجم البيانات المجلوبة
     * فقط، أداء بحت). التحقق الدقيق (محلي/عالمي، دوام، DND، استعجال)
     * **كله** عبر ProviderEligibilityService::isEligible() — نفس
     * بالضبط الدالة المستخدمة بـ getRequestDetails() وبـ Job
     * الإشعارات، بدون أي نسخة موازية ممكن تنحرف عنها مستقبلاً.
     */
    public function getEligibleRequests(ServiceProvider $provider, array $filters = []): LengthAwarePaginator
    {
        if ($provider->account_status !== 'active' || $provider->availability_status === 'offline') {
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

