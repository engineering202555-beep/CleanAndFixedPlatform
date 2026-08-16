<?php

namespace App\Http\Resources\ServiceProvider;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProviderComplaintResource extends JsonResource
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

            'reason' => $this->reason,

            'description' => $this->description,

            'status' => $this->status,

            'admin_notes' => $this->admin_notes,

             'against_user' => $this->againstUser
                ? [
                    'id' => $this->againstUser->id,
                    'first_name' =>
                        $this->againstUser->first_name,
                    'last_name' =>
                        $this->againstUser->last_name,
                ]
                : null,

            'service_request_id' =>
                $this->service_request_id,

            'created_at' =>
                $this->created_at,
        ];
    }
}

        
