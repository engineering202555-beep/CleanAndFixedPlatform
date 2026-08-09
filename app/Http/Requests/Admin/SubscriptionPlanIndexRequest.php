<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class SubscriptionPlanIndexRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'type'      => ['sometimes', 'in:free,paid'],
            'is_active' => ['sometimes', 'boolean'],
            'per_page'  => ['sometimes', 'integer', 'min:5', 'max:100'],
        ];
    }
}
