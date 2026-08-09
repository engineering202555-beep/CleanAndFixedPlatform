<?php

namespace App\Http\Resources\Admin;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ComplaintDetailResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'              => $this->id,
            'reason'          => $this->reason,
            'description'     => $this->description,
            'status'          => $this->status,
            'admin_notes'     => $this->admin_notes,
            'created_at'      => $this->created_at->toDateTimeString(),
            'complainant'     => $this->formatParty($this->user),
            'against_user'    => $this->againstUser ? $this->formatParty($this->againstUser) : null,
            // service_request_id أصلاً Nullable — بيرجع null كامل
            // لو الشكوى مش مرتبطة بطلب، بدل ما نفترض وجوده دايماً.
            'service_request' => $this->when($this->serviceRequest !== null, function () {
                $sr = $this->serviceRequest;

                return [
                    'id'           => $sr->id,
                    'status'       => $sr->status,
                    'request_type' => $sr->request_type,
                    'created_at'   => $sr->created_at->toDateTimeString(),
                    'category'     => $sr->serviceCategory?->name,
                    'area'         => $sr->serviceArea ? [
                        'area_name' => $sr->serviceArea->area_name,
                        'city'      => $sr->serviceArea->city,
                    ] : null,
                    'customer'     => $sr->customer ? [
                        'id'        => $sr->customer->id,
                        'full_name' => trim($sr->customer->user->first_name.' '.$sr->customer->user->last_name),
                    ] : null,
                    // مقدم الخدمة مش عمود مباشر بالطلب، بيُستنتج من
                    // العرض المقبول (acceptedOffer) — ممكن يكون null
                    // لو الطلب لسا بمرحلة بحث ولا في عرض مقبول بعد.
                    'provider'     => $sr->acceptedOffer?->serviceProvider ? [
                        'id'        => $sr->acceptedOffer->serviceProvider->id,
                        'full_name' => trim(
                            $sr->acceptedOffer->serviceProvider->user->first_name.' '
                            .$sr->acceptedOffer->serviceProvider->user->last_name
                        ),
                    ] : null,
                ];
            }),
        ];
    }

    private function formatParty(User $user): array
    {
        $role = $user->serviceProvider()->exists()
            ? 'provider'
            : ($user->customer()->exists() ? 'customer' : 'unknown');

        return [
            'id'           => $user->id,
            'full_name'    => trim($user->first_name.' '.$user->last_name),
            'phone_number' => $user->phone_number,
            'role'         => $role,        ];
    }
}
