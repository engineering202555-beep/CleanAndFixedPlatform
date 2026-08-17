<?php

namespace App\Http\Resources\ServiceProvider;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CurrentSubscriptionResource extends JsonResource
{
    public function toArray(Request $request): array
    {

        $isPending = $this->status === 'pending_payment';

        return [
            'id'        => $this->id,
            'plan_type' => $this->subscription->type,
            'status'    => $this->status,

            // بحالة pending_payment، starts_at/ends_at المخزّنة
            // بقاعدة البيانات مجرد قيم مؤقتة (العمودين NOT NULL،
            // ما فيهن يكونوا فاضيين تقنياً) — مش تواريخ حقيقية بعد،
            // فنرجعهم null صراحة بدل ما نعرض تاريخ وهمي يوحي إنه
            // الاشتراك فعّال فعلاً.
            'starts_at' => $isPending ? null : $this->starts_at?->toDateTimeString(),
            'ends_at'   => $isPending ? null : $this->ends_at?->toDateTimeString(),

            'used_requests' => $this->used_requests,

            // requests_limit الحقيقي (Snapshot) لسا null لحد التفعيل
            // الفعلي — بالانتظار، نعرض "المتوقع" من الخطة نفسها بس
            // للعرض (مش نفس القيمة المحفوظة بالـ DB، توضيح بصري بس).
            'requests_limit' => $this->requests_limit ?? $this->subscription->requests_per_month,

            'is_complimentary' => (bool) $this->is_complimentary,

            // حقل جديد يوضح للفرونت إند إنه الأرقام فوق "متوقعة"
            // مش نهائية بعد، بدل ما يفترض هو نفسه.
            'is_pending_activation' => $isPending,
        ];
    }
}
