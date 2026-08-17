<?php

namespace App\Services\ServiceRequest;

use App\Events\ProviderUpdatedRequestStatus;
use App\Models\Offer;
use App\Models\ServiceProvider;
use App\Models\ServiceRequest;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;

class ProviderRequestActionService
{
    private const START_TRANSITIONS = [
        'accepted'            => 'in_progress',
        'scheduled'           => 'in_progress',
        'inspection_accepted' => 'inspection_in_progress',
    ];

    private const FINISH_TRANSITIONS = [
        'inspection_in_progress' => 'fault_detected',
    ];

    private const COMPLETE_FROM = ['awaiting_confirmation'];

    private const CANCEL_FROM = [
        'accepted', 'scheduled',
        'inspection_accepted',
    ];

    /**
     * أي حالة من هدول تعتبر "مقدم الخدمة لسا مرتبط بشغل نشط" —
     * مستخدمة لتحديد هل نرجّعه available أو لأ (نقطة 18).
     */
    private const ACTIVE_ENGAGEMENT_STATUSES = [
        'accepted', 'scheduled', 'in_progress',
        'inspection_accepted', 'inspection_in_progress',
    ];

    public function start(ServiceProvider $provider, ServiceRequest $request): ServiceRequest
    {
        return $this->applyTransition($provider, $request, self::START_TRANSITIONS, forceAvailability: 'busy');
    }

    /**
     * استخدام وحيد فعلي: إنهاء زيارة الكشف (inspection_in_progress
     * → fault_detected). لو استُدعيت على طلب بحالة تانية (مثلاً
     * in_progress)، هترجع Conflict — مش لأنه في مشكلة تقنية، إنما
     * لأنه هاد الانتقال أصلاً مش من صلاحيات Provider.
     */
    public function finish(ServiceProvider $provider, ServiceRequest $request): ServiceRequest
    {
        return $this->applyTransition($provider, $request, self::FINISH_TRANSITIONS, forceAvailability: null);
    }

    public function complete(ServiceProvider $provider, ServiceRequest $request): ServiceRequest
    {
        $map = array_fill_keys(self::COMPLETE_FROM, 'completed');

        return $this->applyTransition($provider, $request, $map, forceAvailability: null);
    }
    public function cancel(ServiceProvider $provider, ServiceRequest $request): ServiceRequest
    {
        $map = array_fill_keys(self::CANCEL_FROM, 'cancel_by_provider');

        return $this->applyTransition($provider, $request, $map, forceAvailability: null);
    }

    /**
     * forceAvailability:
     * - 'busy'  → دايماً بيصير busy (start: أكيد بلّش شغل جديد).
     * - null    → لازم نتحقق "هل عندو شغل تاني نشط؟" قبل ما نرجّعه
     *             available (finish/complete/cancel) — هاي بالضبط
     *             نقطة 18: طلب B لسا شغال ما لازم يتأثر بانتهاء A.
     */
    private function applyTransition(
        ServiceProvider $provider,
        ServiceRequest $request,
        array $transitionsMap,
        ?string $forceAvailability
    ): ServiceRequest {
        $updatedRequest = DB::transaction(function () use ($provider, $request, $transitionsMap, $forceAvailability) {
            $lockedRequest = ServiceRequest::whereKey($request->getKey())
                ->lockForUpdate()
                ->firstOrFail();

            $this->ensureOwnership($provider, $lockedRequest);

            $newStatus = $transitionsMap[$lockedRequest->status] ?? null;

            if ($newStatus === null) {
                throw new ConflictHttpException(
                    "لا يمكن تنفيذ هذا الإجراء والطلب بحالة '{$lockedRequest->status}'."
                );
            }

            $lockedRequest->update(['status' => $newStatus]);

            $this->syncAvailability($provider, $forceAvailability);

            return $lockedRequest;
        });

        event(new ProviderUpdatedRequestStatus($updatedRequest, $updatedRequest->status));

        return $updatedRequest;
    }

    /**
     * lockForUpdate على صف الـ Provider نفسه (مش بس صف الطلب) —
     * ضروري هون تحديداً: لو طلبين لنفس المزوّد خلصوا بنفس اللحظة
     * تقريباً بضغطتين متزامنتين، لازم فحص "هل عندو شغل تاني؟"
     * يصير متسلسل، مش متوازي، وإلا الاثنين ممكن يشوفوا "مافي شغل
     * تاني" بنفس اللحظة (كل وحدة قبل ما التانية تلتزم) ويرجعوا
     * available بالغلط بينما بالحقيقة لسا في تعارض.
     */
    private function syncAvailability(ServiceProvider $provider, ?string $forceAvailability): void
    {
        $lockedProvider = ServiceProvider::whereKey($provider->getKey())->lockForUpdate()->first();

        if ($forceAvailability === 'busy') {
            $lockedProvider->update(['availability_status' => 'busy']);

            return;
        }

        $hasOtherActiveWork = ServiceRequest::query()
            ->whereHas('acceptedOffer', function ($query) use ($lockedProvider) {
                $query->where('service_provider_id', $lockedProvider->id);
            })
            ->whereIn('status', self::ACTIVE_ENGAGEMENT_STATUSES)
            ->exists();

        if (! $hasOtherActiveWork) {
            $lockedProvider->update(['availability_status' => 'available']);
        }
        // لو عندو شغل تاني نشط، نسيب availability_status متل ما هي
        // (busy) — صفر تحديث، بدل ما نرجّعه available بالغلط.
    }

    private function ensureOwnership(ServiceProvider $provider, ServiceRequest $request): void
    {
        /** @var Offer|null $acceptedOffer */
        $acceptedOffer = $request->acceptedOffer;

        if (! $acceptedOffer || $acceptedOffer->service_provider_id !== $provider->id) {
            throw new AccessDeniedHttpException('هذا الطلب غير مرتبط بك.');
        }
    }
}
