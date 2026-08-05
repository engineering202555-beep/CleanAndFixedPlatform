<?php

namespace App\Http\Resources\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BlockedProviderResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $interactionsCount = (int) $this->interactions_count;

        return [
            'id'                => $this->id,
            'customer_name'     => trim($this->customer->user->first_name.' '.$this->customer->user->last_name),
            'provider_name'     => trim($this->serviceProvider->user->first_name.' '.$this->serviceProvider->user->last_name),
            'provider_image'    => $this->serviceProvider->profileImage?->path
                ? asset('storage/'.$this->serviceProvider->profileImage->path)
                : null,
            'blocked_at'        => $this->created_at->toDateTimeString(),
            'has_previous_requests' => $interactionsCount > 0,
            // التفاصيل دي بس لما فيه تفاعل سابق فعلي — مافي داعي
            // نرجّع أصفار/null بلا معنى لو ما كان في أي طلب بينهما.
            'previous_requests' => $this->when($interactionsCount > 0, [
                'count'              => $interactionsCount,
                'last_request_id'    => $this->last_request_id,
                'last_request_status' => $this->last_request_status,
                'last_request_date'  => $this->last_request_date,
            ]),
        ];
    }
}
