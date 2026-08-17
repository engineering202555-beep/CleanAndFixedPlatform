<?php

namespace App\Http\Requests\ServiceProvider\ServiceRequest;

use Illuminate\Foundation\Http\FormRequest;

class ListEligibleRequestsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'per_page' => ['sometimes', 'integer', 'min:5', 'max:100'],
        ];
    }
}
