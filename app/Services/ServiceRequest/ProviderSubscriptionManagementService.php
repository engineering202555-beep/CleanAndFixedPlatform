<?php

namespace App\Services\ServiceRequest;

use App\Models\ServiceProvider;
use App\Models\Subscription;
use App\Models\SubscriptionProvider;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;

class ProviderSubscriptionManagementService
{
    private const FREE_PLAN_TYPE = 'free';

    /**
     * إنشاء صف pending_payment بس — التفعيل الفعلي (starts_at الحقيقي،
     * حساب ends_at، Snapshot السعر) بيصير حصراً عبر
     * SubscriptionActivationService الموجود أصلاً بجانب الأدمن، بعد
     * ما يتأكد من استلام الدفع يدوياً. صفر تكرار منطق تفعيل هون.
     */
    public function selectPlan(ServiceProvider $provider, int $subscriptionId): SubscriptionProvider
    {
        return DB::transaction(function () use ($provider, $subscriptionId) {
            $lockedProvider = ServiceProvider::whereKey($provider->getKey())->lockForUpdate()->firstOrFail();

            $hasPendingRequest = SubscriptionProvider::query()
                ->where('service_provider_id', $lockedProvider->id)
                ->where('status', 'pending_payment')
                ->exists();

            if ($hasPendingRequest) {
                throw new ConflictHttpException('لديك طلب اشتراك قيد انتظار موافقة الإدارة مسبقاً.');
            }

            // starts_at/ends_at هون قيم مؤقتة بس (العمودين NOT NULL
            // بالجدول) — بتُستبدَل بالكامل وقت التفعيل الحقيقي عبر
            // SubscriptionActivationService.
            return SubscriptionProvider::create([
                'service_provider_id' => $lockedProvider->id,
                'subscription_id'     => $subscriptionId,
                'starts_at'           => now(),
                'ends_at'             => now(),
                'status'              => 'pending_payment',
                'used_requests'       => 0,
                'is_complimentary'    => false,
            ]);
        });
    }

    /**
     * إلغاء ذكي حسب الحالة الحالية:
     * 1) في طلب pending_payment؟ → إلغاؤه بس (سحب الطلب قبل ما
     *    الأدمن يعالجه)، الاشتراك الحالي (أياً كان) ما بيتأثر.
     * 2) وإلا، عندو اشتراك active مدفوع؟ → يُلغى، ويُمنح فوراً
     *    اشتراك مجاني نشط (بدون انتظار أدمن — مجاني، صفر دفع).
     * 3) وإلا (أصلاً على المجانية أو ما عنده شي) → لا شي للإلغاء.
     */
    public function cancelSubscription(ServiceProvider $provider): SubscriptionProvider
    {
        return DB::transaction(function () use ($provider) {
            $lockedProvider = ServiceProvider::whereKey($provider->getKey())->lockForUpdate()->firstOrFail();

            $pendingRequest = SubscriptionProvider::query()
                ->where('service_provider_id', $lockedProvider->id)
                ->where('status', 'pending_payment')
                ->lockForUpdate()
                ->first();

            if ($pendingRequest) {
                $pendingRequest->update(['status' => 'cancelled']);

                return $pendingRequest;
            }

            $activeSubscription = SubscriptionProvider::query()
                ->where('service_provider_id', $lockedProvider->id)
                ->where('status', 'active')
                ->with('subscription:id,type')
                ->lockForUpdate()
                ->first();

            if (! $activeSubscription || $activeSubscription->subscription->type === self::FREE_PLAN_TYPE) {
                throw new ConflictHttpException('لا يوجد اشتراك مدفوع نشط لإلغائه.');
            }

            $activeSubscription->update(['status' => 'cancelled', 'ends_at' => now()]);

            return $this->grantFreePlan($lockedProvider);
        });
    }

    private function grantFreePlan(ServiceProvider $provider): SubscriptionProvider
    {
        $freePlan = Subscription::query()->where('type', self::FREE_PLAN_TYPE)->firstOrFail();

        return SubscriptionProvider::create([
            'service_provider_id' => $provider->id,
            'subscription_id'     => $freePlan->id,
            'starts_at'           => now(),
            'ends_at'             => now()->addDays($freePlan->duration_in_days),
            'status'              => 'active',
            'used_requests'       => 0,
            'is_complimentary'    => false,
        ]);
    }
}
