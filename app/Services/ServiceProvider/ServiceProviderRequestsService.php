<?php

namespace App\Services\ServiceProvider;

use App\Models\ServiceProvider;
use App\Models\Subscription;
use App\Models\SubscriptionProvider;
use Illuminate\Support\Facades\DB;
use RuntimeException;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;

class ServiceProviderRequestsService
{
    private const FREE_SUBSCRIPTION_TYPE = 'free';

    /**
     * نقطة الدخول للقرار الأول على طلب انضمام جديد (لسا pending).
     */
    public function decide(ServiceProvider $provider, array $data): void
    {
        $this->ensureStatus($provider, 'pending', 'تم اتخاذ قرار على هذا الطلب مسبقاً.');

        match ($data['status']) {
            'approved' => $this->approve($provider),
            'rejected' => $this->reject($provider, $data['reason'] ?? null),
        };
    }

    /**
     * نقطة الدخول لإعادة النظر بطلب مرفوض سابقاً: إما نقلبه لمقبول
     * (بيمر بنفس منطق approve() تماماً)، أو نتركه مرفوض مع إمكانية
     * تحديث سبب الرفض (مثلاً توضيح إضافي بعد تواصل مع مقدم الخدمة).
     */
    public function reconsider(ServiceProvider $provider, array $data): void
    {
        $this->ensureStatus($provider, 'rejected', 'هذا الطلب غير مرفوض حالياً، لا يمكن إعادة النظر فيه.');

        match ($data['status']) {
            'approved' => $this->approve($provider),
            'rejected' => $this->reject($provider, $data['reason'] ?? $provider->rejection_reason),
        };
    }

    private function ensureStatus(ServiceProvider $provider, string $expectedStatus, string $message): void
    {
        if ($provider->account_status !== $expectedStatus) {
            throw new ConflictHttpException($message);
        }
    }

    private function approve(ServiceProvider $provider): void
    {
        DB::transaction(function () use ($provider) {
            // إعادة الجلب مع قفل الصف يمنع تنفيذ الموافقة مرتين
            // بحال ضغط الأدمن مرتين بسرعة أو فتح تبويبين.
            $lockedProvider = ServiceProvider::whereKey($provider->getKey())
                ->lockForUpdate()
                ->firstOrFail();

            // هنا الشرط الوحيد المطلوب هو "مش مقبول فعلاً"، بغض النظر
            // إذا جاي من decide() (كان pending) أو reconsider() (كان rejected)،
            // لأنه approve() أصبحت مشتركة بين المسارين.
            if ($lockedProvider->account_status === 'active') {
                throw new ConflictHttpException('مقدم الخدمة موافق عليه مسبقاً.');
            }

            $lockedProvider->update([
                'account_status' => 'active',
                'availability_status' => 'offline',
                'rejection_reason' => null,
            ]);

            $lockedProvider->user->assignRole('provider');

            $this->attachFreeSubscription($lockedProvider);
        });
    }

    private function reject(ServiceProvider $provider, ?string $reason): void
    {
        $provider->update([
            'account_status' => 'rejected',
            'availability_status' => 'offline',
            'rejection_reason' => $reason,
        ]);
    }

    private function attachFreeSubscription(ServiceProvider $provider): void
    {
        $subscription = Subscription::firstWhere('type', self::FREE_SUBSCRIPTION_TYPE);

        if (! $subscription) {
            throw new RuntimeException(
                'خطة الاشتراك المجانية غير معرّفة بقاعدة البيانات، راجع بيانات الإعداد الأساسية.'
            );
        }

        SubscriptionProvider::create([
            'service_provider_id' => $provider->id,
            'subscription_id'     => $subscription->id,
            // كانوا ناقصين — نفس منطق Snapshot المطبّق بكل مكان تاني
            // (SubscriptionActivationService)، هون بس القيم صفرية
            // منطقياً لأنها الخطة المجانية.
            'price_paid'          => $subscription->price,
            'requests_limit'      => $subscription->requests_per_month,
            'starts_at'           => now(),
            'ends_at'             => now()->addDays($subscription->duration_in_days),
            'status'              => 'active',
            'used_requests'       => 0,
        ]);
    }
}
