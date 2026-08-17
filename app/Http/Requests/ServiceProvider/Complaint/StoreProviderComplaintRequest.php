<?php

namespace App\Http\Requests\ServiceProvider\Complaint;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
class StoreProviderComplaintRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
           

            'service_request_id' => [
               'required',
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
            ],
        ];
    }
}
   
