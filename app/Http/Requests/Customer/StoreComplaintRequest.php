<?php

namespace App\Http\Requests\Customer;

use Illuminate\Foundation\Http\FormRequest;

class StoreComplaintRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
        
            'service_request_id' => [
                'required',
                'exists:service_requests,id',
            ],

            'reason' => [
                'required',
                'in:provider_behavior,poor_service,late_arrival,payment_issue,technical_problem,other',
            ],

            'description' => [
                'required',
                'string',
                'min:5',
            ],
        ];
    }
}