<?php

namespace App\Services\Subscription;

use App\Models\SubscriptionProvider;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;

class SubscriptionActivationService
{
    /**
     * تفعيل اشتراك بعد استلام الدفع يدوياً من الأدمن. بشرط إجباري:
     * الحالة الحالية pending_payment بالضبط — يمنع إعادة تفعيل
     * اشتراك فعّال أصلاً أو ملغى سابقاً.
     */
    public function activate(SubscriptionProvider $subscriptionProvider): SubscriptionProvider
    {
        return DB::transaction(function () use ($subscriptionProvider) {
            // إعادة الجلب مع قفل الصف يمنع تفعيل مزدوج بضغطة متكررة
            $locked = SubscriptionProvider::query()
                ->with('subscription')
                ->whereKey($subscriptionProvider->getKey())
                ->lockForUpdate()
                ->firstOrFail();

            $this->ensurePendingPayment($locked);

            // منع وجود أكثر من اشتراك active بنفس الوقت لنفس مقدم
            // الخدمة — أي اشتراك فعّال سابق ينتهي فوراً عند تفعيل جديد.
            SubscriptionProvider::query()
                ->where('service_provider_id', $locked->service_provider_id)
                ->where('status', 'active')
                ->update(['status' => 'cancelled', 'ends_at' => now()]);

            $startsAt = now();

            $locked->update([
                'status'         => 'active',
                'starts_at'      => $startsAt,
                'ends_at'        => $startsAt->copy()->addDays($locked->subscription->duration_in_days),
                'price_paid'     => $locked->subscription->price,
                'requests_limit' => $locked->subscription->requests_per_month,
            ]);

            return $locked;
        });
    }

    private function ensurePendingPayment(SubscriptionProvider $subscriptionProvider): void
    {
        if ($subscriptionProvider->status !== 'pending_payment') {
            $message = $subscriptionProvider->status === 'active'
                ? 'هذا الاشتراك مفعّل أصلاً.'
                : 'هذا الاشتراك ملغى، لا يمكن إعادة تفعيله.';

            throw new ConflictHttpException($message);
        }
    }
}
