<?php

namespace App\Services\Offer;

use App\Models\Offer;
use App\Models\ServiceProvider;
use App\Models\ServiceRequest;
use App\Services\ServiceRequest\ProviderEligibilityService;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;

class OfferService
{
    private const OFFER_VALIDITY_MINUTES = 30;

    // الحالات يلي أول عرض (أو عرض تصليح بعد الكشف) بيطلّعها لـ processing
    private const OFFER_TRIGGERS_PROCESSING = ['pending_local', 'pending_global', 'fault_detected'];

    public function __construct(
        private readonly ProviderEligibilityService $eligibilityService
    ) {
    }

    public function createOffer(ServiceProvider $provider, ServiceRequest $request, array $data): Offer
    {
        return DB::transaction(function () use ($provider, $request, $data) {
            $lockedRequest = ServiceRequest::whereKey($request->getKey())
                ->lockForUpdate()
                ->firstOrFail();

            $this->ensureCanOffer($provider, $lockedRequest);

            // فحص التكرار: عرض pending واحد بس بأي لحظة لنفس (مزوّد
            // + طلب) — لا "أبداً بتاريخ حياة الطلب"، لأنه سيناريو
            // الكشف→التصليح بالتصميم بيحتاج عرضين متتاليين من نفس
            // المزوّد (كشف، وبعدين تصليح)، بس أبداً وحدة "pending"
            // مكررة بنفس اللحظة.
            $hasPendingOffer = Offer::query()
                ->where('service_request_id', $lockedRequest->id)
                ->where('service_provider_id', $provider->id)
                ->where('status', 'pending')
                ->exists();

            if ($hasPendingOffer) {
                throw new ConflictHttpException('لديك عرض قيد الانتظار على هذا الطلب مسبقاً.');
            }

            $price = $this->resolvePrice($provider, $lockedRequest, $data);

            $offer = Offer::create([
                'service_provider_id' => $provider->id,
                'service_request_id'  => $lockedRequest->id,
                'price'                => $price,
                'estimated_duration'   => $data['estimated_duration'] ?? null,
                'notes'                => $data['notes'] ?? null,
                'status'               => 'pending',
                'duration_in_minutes'  => self::OFFER_VALIDITY_MINUTES,
                'expires_at'           => now()->addMinutes(self::OFFER_VALIDITY_MINUTES),
            ]);

            if (in_array($lockedRequest->status, self::OFFER_TRIGGERS_PROCESSING, true)) {
                $lockedRequest->update(['status' => 'processing']);
            }

            return $offer;
        });
    }

    /**
     * فحص الأهلية: حالتين مختلفتين تماماً حسب مرحلة الطلب.
     *
     * 1) طلب لسا بمرحلة بحث (pending_*): أي مزوّد مؤهل (تصنيف/منطقة/
     *    دوام/DND) بيقدر يبعت عرض — نفس القاعدة العامة.
     *
     * 2) طلب fault_detected (بعد كشف مكتمل): بس نفس المزوّد يلي
     *    عمل الكشف تحديداً (مالك acceptedOffer الأصلي) بيقدر يبعت
     *    عرض التصليح — منع أي مزوّد تاني "يخطف" الطلب بعد ما زميله
     *    كشف عليه.
     */
    private function ensureCanOffer(ServiceProvider $provider, ServiceRequest $request): void
    {
        if ($request->status === 'fault_detected') {
            $diagnosticOffer = $request->acceptedOffer;

            if (! $diagnosticOffer || $diagnosticOffer->service_provider_id !== $provider->id) {
                throw new AccessDeniedHttpException('هذا الطلب مرتبط بمقدم خدمة آخر قام بالكشف عليه.');
            }

            return;
        }

        if (! $this->eligibilityService->isEligible($provider, $request)) {
            throw new AccessDeniedHttpException('هذا الطلب غير متاح لك حالياً.');
        }
    }

    /**
     * أثناء مرحلة الكشف الأولى (عطل غير محدد، لسا بمرحلة بحث):
     * السعر = inspection_price تبع بروفايل مقدم الخدمة تلقائياً،
     * بغض النظر شو أرسل بالـ Request (حتى لو الفرونت إند غلط
     * وبعت سعر، بنتجاهله بالكامل لصالح البروفايل — مصدر الحقيقة
     * الوحيد لسعر الكشف هو البروفايل، مش إدخال يدوي بكل مرة).
     *
     * أي حالة تانية (طلب عادي، أو عرض تصليح بعد fault_detected):
     * السعر يدوي من الـ Request، إجباري (already validated).
     */
    private function resolvePrice(ServiceProvider $provider, ServiceRequest $request, array $data): ?float
    {
        $isDiagnosticPhase = $request->request_type === 'unspecified_fault'
            && in_array($request->status, ['pending_local', 'pending_global'], true);

        if ($isDiagnosticPhase) {
            return (float) $provider->inspection_price;
        }

        return $data['price'] ?? null;
    }
}
