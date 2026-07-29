<?php

namespace App\Services\Subscription;

use App\Models\ServiceProvider;
use App\Models\Subscription;
use App\Models\SubscriptionProvider;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;

class SubscriptionService
{
    /**
     * منح مقدم خدمة اشتراك مدفوع فعلياً لمدة شهر واحد بدون تحصيل،
     * بشرط ما يكون عنده اشتراك مدفوع فعّال حالياً لم تنتهِ مدته.
     */
    public function grantComplimentaryMonth(ServiceProvider $provider, array $data): SubscriptionProvider
    {
        $plan = Subscription::findOrFail($data['subscription_id']);


        return DB::transaction(function () use ($provider, $plan, $data) {
            // lockForUpdate يمنع منح شهرين مجانيين بضغطة مزدوجة بنفس اللحظة
            $lockedProvider = ServiceProvider::whereKey($provider->getKey())
                ->lockForUpdate()
                ->firstOrFail();

            $this->ensureEligibleForComplimentary($lockedProvider);

            // أي اشتراك نشط حالياً (غالباً الخطة المجانية) ينتهي فوراً
            $lockedProvider->subscriptions()
                ->where('status', 'active')
                ->update([
                    'status' => 'cancelled',
                    'ends_at' => now(),
                ]);

            return SubscriptionProvider::create([
                'service_provider_id' => $lockedProvider->id,
                'subscription_id' => $plan->id,
                'starts_at' => now(),
                'ends_at' => now()->addMonth(), // شهر ثابت، مش duration_in_days الخطة
                'status' => 'active',
                'used_requests' => 0,
                'is_complimentary' => true,
            ]);
        });
    }

    /**
     * شرطين إجباريين معاً، مش شرط واحد:
     * 1) لازم يكون اشترك بخطة مدفوعة سابقاً على الأقل مرة (هذا نظام
     *    مكافأة لمين دفع فعلاً، مش لمين ظل على المجاني من الأساس).
     * 2) وما يكون عنده اشتراك مدفوع فعّال حالياً لم تنتهِ مدته.
     */
    private function ensureEligibleForComplimentary(ServiceProvider $provider): void
    {
        if ($provider->account_status !== 'active') {
            throw new ConflictHttpException(
                'لا يمكن منح اشتراك إلا لمقدم خدمة بحساب فعال.'
            );
        }

        $hasEverPaid = $provider->subscriptions()
            ->whereHas('subscription', function ($query) {
                $query->where('type', 'paid');
            })
            ->exists();

        if (! $hasEverPaid) {
            throw new ConflictHttpException(
                'هذا العرض مخصص فقط لمقدمي الخدمة الذين سبق واشتركوا بخطة مدفوعة.'
            );
        }

        $hasActivePaidSubscription = $provider->subscriptions()
            ->where('status', 'active')
            ->where('ends_at', '>', now())
            ->whereHas('subscription', function ($query) {
                $query->where('type', 'paid');
            })
            ->exists();

        if ($hasActivePaidSubscription) {
            throw new ConflictHttpException(
                'لا يمكن منح شهر مجاني لأن مقدم الخدمة لديه اشتراك مدفوع فعّال لم تنتهِ مدته بعد.'
            );
        }
    }
}
