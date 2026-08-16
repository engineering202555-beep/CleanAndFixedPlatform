<?php

namespace App\Http\Resources\ServiceProvider;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProviderProfileResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $provider = $this->serviceProvider;

        return [
            'id'                  => $this->id,
            'first_name'          => $this->first_name,
            'last_name'           => $this->last_name,
            'full_name'           => trim($this->first_name.' '.$this->last_name),
            'phone_number'        => $this->phone_number,
            'phone_verified'      => (bool) $this->phone_verified_at,

            'account_status'      => $provider?->account_status,
            'rejection_reason'    => $provider?->rejection_reason,

            'service_category_id' => $provider?->service_category_id,
            'service_category'    => $provider?->serviceCategory?->name,
            'service_area_id'     => $provider?->service_area_id,
            'service_area'        => $provider?->serviceArea?->area_name,
            'city'                => $provider?->serviceArea?->city,

            'inspection_price'    => $provider ? (float) $provider->inspection_price : null,
            'bio'                 => $provider?->bio,
            'experience_years'    => $provider?->experience_years,
            'working_from'        => $provider?->working_from?->format('H:i'),
            'working_to'          => $provider?->working_to?->format('H:i'),
            'latitude'            => $provider?->latitude !== null ? (float) $provider->latitude : null,
            'longitude'           => $provider?->longitude !== null ? (float) $provider->longitude : null,

            'profile_image_url'   => $provider?->images->firstWhere('type', 'profile')?->url,
            'document_urls'       => $provider?->images->where('type', 'documents')->pluck('url')->values(),
        ];
    }
}
