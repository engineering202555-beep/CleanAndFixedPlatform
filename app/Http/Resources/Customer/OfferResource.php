<?php

namespace App\Http\Resources\Customer;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OfferResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [

            'id' => $this->id,

            // بيانات مقدم الخدمة
            'service_provider' => [

                'id' => $this->serviceProvider->id,

                'first_name' => $this->serviceProvider->user->first_name,

                //'profile_image' => optional(
                   // $this->serviceProvider
                        //->user
                        //->images()
                        //->where('type', 'profile')
                       // ->first()
                //)->path,

                'rating' => $this->serviceProvider->rating,

                //'reviews_count' => $this->serviceProvider
                   // ->reviews()
                  //  ->count(),

                'experience_years' => $this->serviceProvider
                    ->experience_years,

                'bio' => $this->serviceProvider->bio,

            ],

            // بيانات العرض
            'price' => $this->price,

            'estimated_duration' => $this->estimated_duration,

            'notes' => $this->notes,

            'status' => $this->status,

            'starts_at' => $this->starts_at,

            'duration_in_minutes' => $this->duration_in_minutes,

            'expires_at' => $this->expires_at,
             'distance' => $this->distance,
            'created_at' => $this->created_at,
        ];
    }
}