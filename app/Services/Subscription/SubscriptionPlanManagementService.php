<?php

namespace App\Services\Subscription;

use App\Models\Subscription;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;

class SubscriptionPlanManagementService
{
    public function store(array $data): Subscription
    {
        return Subscription::create($data);
    }

    /**
     * تعديل السعر/عدد الطلبات/المدة هون **ما بيأثر على أي اشتراك
     * قائم فعلاً**، لأنه القيم الفعلية محفوظة كـ Snapshot
     * (price_paid, requests_limit) بجدول subscription_providers
     * نفسه وقت التفعيل. هاد التعديل بيأثر بس على أي تفعيل جديد
     * بعد هيك.
     */
    public function update(Subscription $subscription, array $data): Subscription
    {
        $subscription->update($data);

        return $subscription;
    }

    /**
     * حذف حقيقي مسموح فقط لخطة ما استُخدمت ولا مرة إطلاقاً (بغض
     * النظر عن الحالة). أي استخدام سابق ولو منتهي أو ملغى = تعطيل
     * بس (is_active=false)، مش حذف — وهذا أصلاً مطابق لما تفرضه
     * restrictOnDelete على العمود بقاعدة البيانات، إحنا بس عم نتحقق
     * منها مبكراً برسالة واضحة بدل خطأ SQL فظّ.
     */
    public function delete(Subscription $subscription): void
    {
        if ($subscription->providerSubscriptions()->exists()) {
            throw new ConflictHttpException(
                'لا يمكن حذف خطة اشتراك مرتبطة بمقدمي خدمة (حالياً أو سابقاً). عطّل الخطة بدلاً من ذلك.'
            );
        }

        $subscription->delete();
    }

    public function deactivate(Subscription $subscription): Subscription
    {
        $subscription->update(['is_active' => false]);

        return $subscription;
    }

    public function activate(Subscription $subscription): Subscription
    {
        $subscription->update(['is_active' => true]);

        return $subscription;
    }
}
