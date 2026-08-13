<?php

namespace App\Http\Resources\Customer;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProfileResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [

            'id' => $this->id,

            'first_name' => $this->user->first_name,
            'last_name' => $this->user->last_name,

            'phone_number' => $this->user->phone_number,

         

           /* 'profile_image' => optional(
                $this->user
                    ->images()
                    ->where('type', 'profile')
                    ->first()
            )->path,*/

            'service_area' => $this->serviceArea    //
                ? [
                    'id' => $this->serviceArea->id,
                    'name' => $this->serviceArea->area_name,
                ]
                : null,

        ];
    }
}