<?php

namespace App\Services\ServiceProvider;

use App\Models\ServiceProvider;
use App\Models\Subscription;
use App\Models\SubscriptionProvider;
use Illuminate\Support\Facades\DB;
use RuntimeException;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;

class ServiceProvidersManageService
{
    private const FREE_SUBSCRIPTION_TYPE = 'free';

    /**
     * حذف ناعم (Soft Delete) لمقدم خدمة، مسموح بس بحالتين:
     * ما يكون "مشغول" حالياً (availability_status)، وما يكون
     * عنده أي طلب نشط لم يُنهَ بعد (سواء بحالة accepting أو in_progress).
     */
    public function delete(ServiceProvider $provider): void
    {
        $this->ensureDeletable($provider);

        DB::transaction(function () use ($provider) {
            $provider->delete();
            $provider->user->delete();
        });
    }

    private function ensureDeletable(ServiceProvider $provider): void
    {
        if ($provider->availability_status === 'busy') {
            throw new ConflictHttpException(
                'لا يمكن حذف مقدم خدمة مشغول حالياً بطلب نشط.'
            );
        }

        if ($this->hasUnfinishedRequests($provider)) {
            throw new ConflictHttpException(
                'لا يمكن حذف مقدم خدمة لديه طلبات مقبولة لم تكتمل بعد.'
            );
        }

        if (! in_array($provider->account_status, ['active', 'blocked'], true)) {
            throw new ConflictHttpException(
                'لا يمكن حذف مقدم خدمة بانتظار المراجعة أو مرفوض.'
            );
        }
    }

    /**
     * التحقق من availability_status وحده غير كافٍ (ممكن يكون قديم
     * أو انحدّث بالغلط)، فهذا الفحص هو مصدر الحقيقة الفعلي: هل يوجد
     * عرض مقبول (accepted) مرتبط بطلب لسا مش بحالة نهائية؟
     */
    private function hasUnfinishedRequests(ServiceProvider $provider): bool
    {
        return $provider->offers()
            ->where('status', 'accepted')
            ->whereHas('serviceRequest', function ($query) {
                $query->whereNotIn('status', [
                    'completed',
                    'rejected',
                    'cancel_by_customer',
                    'cancel_by_provider',
                    'cancel_by_system',
                ]);
            })
            ->exists();
    }

    public function block(ServiceProvider $provider, array $data): void
    {
        $this->ensureBlockable($provider);

        $provider->update([
            'account_status' => 'blocked',
            'availability_status' => 'offline',
            'block_reason' => $data['reason'],
            'blocked_until' => now()->addDays((int)$data['duration_in_days']),
        ]);
    }

    /**
     * فك الحظر يدوياً قبل انتهاء المدة (مثلاً حظر بالغلط، أو تراجع الأدمن).
     */
    public function unblock(ServiceProvider $provider): void
    {
        if ($provider->account_status !== 'blocked') {
            throw new ConflictHttpException('مقدم الخدمة غير محظور حالياً.');
        }

        $provider->update([
            'account_status' => 'active',
            'block_reason' => null,
            'blocked_until' => null,
        ]);
    }

    /**
     * الحظر منطقي بس على مقدم خدمة نشط فعلاً، أو محظور مسبقاً
     * (سماح بتحديث/تمديد مدة حظر قائم). مقدم بانتظار المراجعة أو
     * مرفوض أصلاً مش له علاقة بالمنصة بعد، فالحظر ما بينطبق عليه.
     */
    private function ensureBlockable(ServiceProvider $provider): void
    {
        if (! in_array($provider->account_status, ['active', 'blocked'], true)) {
            throw new ConflictHttpException(
                'لا يمكن حظر مقدم خدمة بانتظار المراجعة أو مرفوض.'
            );
        }

        if ($this->hasUnfinishedRequests($provider)) {
            throw new ConflictHttpException(
                'لا يمكن حظر مقدم خدمة لديه طلبات مقبولة لم تكتمل بعد.'
            );
        }
    }

}
