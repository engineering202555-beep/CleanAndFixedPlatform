<?php

namespace App\Http\Resources\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ReviewResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'              => $this->id,
            'customer_name'   => trim($this->customer->user->first_name.' '.$this->customer->user->last_name),
            'provider_name'   => trim($this->serviceProvider->user->first_name.' '.$this->serviceProvider->user->last_name),
            'provider_image'  => $this->serviceProvider->profileImage?->path
                ? asset('storage/'.$this->serviceProvider->profileImage->path)
                : null,
            'service_category' => $this->serviceRequest->serviceCategory->name,
            'request_id'      => $this->service_request_id,
            'request_type'    => $this->serviceRequest->request_type,
            'comment'         => $this->comment,
            'rating'          => (int) $this->provider_rating,
            'created_at'      => $this->created_at->toDateTimeString(),
        ];
    }
}
