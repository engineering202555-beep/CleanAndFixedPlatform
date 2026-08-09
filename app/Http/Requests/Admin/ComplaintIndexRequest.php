<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class ComplaintIndexRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'status'             => ['sometimes', 'in:pending,in_review,resolved,rejected'],
            'reason'             => ['sometimes', 'in:provider_behavior,poor_service,late_arrival,payment_issue,technical_problem,other'],
            'service_request_id' => ['sometimes', 'integer', 'exists:service_requests,id'],
            'user_id'            => ['sometimes', 'integer', 'exists:users,id'],
            'against_user_id'    => ['sometimes', 'integer', 'exists:users,id'],
            'date_from'          => ['sometimes', 'date', 'required_with:date_to'],
            'date_to'            => ['sometimes', 'date', 'after_or_equal:date_from', 'required_with:date_from'],
            'per_page'           => ['sometimes', 'integer', 'min:5', 'max:100'],
        ];
    }
}
