<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreComplaintRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'against_user_id' => [
                'nullable',
                'exists:users,id',
                'different:' . auth()->id(),
            ],

            'service_request_id' => [
                'nullable',
                'exists:service_requests,id',
            ],

            'reason' => [
                'required',
                Rule::in([
                    'provider_behavior',
                    'poor_service',
                    'late_arrival',
                    'payment_issue',
                    'technical_problem',
                    'other',
                ]),
            ],

            'description' => [
                'required',
                'string',
                'min:10',
                'max:5000',
            ],
        ];
    }
}