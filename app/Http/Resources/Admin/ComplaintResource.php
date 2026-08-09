<?php

namespace App\Http\Resources\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ComplaintResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'                  => $this->id,
            'complainant_name'    => trim($this->user->first_name.' '.$this->user->last_name),
            'against_user_name'   => $this->againstUser
                ? trim($this->againstUser->first_name.' '.$this->againstUser->last_name)
                : null,
            'service_request_id'  => $this->service_request_id,
            'reason'              => $this->reason,
            'description'         => $this->description,
            'status'              => $this->status,
            'admin_notes'         => $this->admin_notes,
            'created_at'          => $this->created_at->toDateTimeString(),
        ];
    }
}
