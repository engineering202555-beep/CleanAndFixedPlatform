<?php

namespace App\Http\Resources\Customer;

use Illuminate\Http\Request\Customer;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
          return [

            'id' => $this->id,

            'first_name' => $this->firstName,

            'last_name' => $this->lastName,


            'phone_number' => $this->phone_number,

            'role' => $this->getRoleNames()->first(),

            'created_at' => $this->created_at,
        ];
    }
}
