<?php

namespace App\Http\Resources\ServiceProvider;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OfferResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'                  => $this->id,
            'service_request_id'  => $this->service_request_id,
            'price'                => $this->price !== null ? (float) $this->price : null,
            'estimated_duration'   => $this->estimated_duration,
            'notes'                => $this->notes,
            'status'               => $this->status,
            // status يضل 'pending' حتى بعد انتهاء الصلاحية (مافي
            // enum قيمة 'expired') — هاد الحقل المحسوب هو يلي بيوضح
            // الحقيقة الفعلية للفرونت إند بدون أي تعديل على البيانات.
            'is_expired'           => $this->status === 'pending' && $this->expires_at?->isPast(),
            'expires_at'           => $this->expires_at?->toDateTimeString(),
            'created_at'           => $this->created_at->toDateTimeString(),
        ];
    }
}
