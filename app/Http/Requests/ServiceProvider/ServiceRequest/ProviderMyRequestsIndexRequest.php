<?php

namespace App\Http\Requests\ServiceProvider\ServiceRequest;

use Illuminate\Foundation\Http\FormRequest;

class ProviderMyRequestsIndexRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'status' => ['sometimes', 'in:'.implode(',', [
                    'pending_local', 'pending_global', 'processing', 'accepted', 'scheduled',
                    'in_progress', 'awaiting_confirmation', 'completed', 'rejected',
                    'cancel_by_customer', 'cancel_by_provider', 'cancel_by_system',
                    'inspection_accepted', 'inspection_in_progress', 'fault_detected',
                ])],
            'request_type' => ['sometimes', 'in:specific_fault,unspecified_fault'],
            'is_urgent'    => ['sometimes', 'boolean'],
            'per_page'     => ['sometimes', 'integer', 'min:5', 'max:100'],
        ];
    }
}
