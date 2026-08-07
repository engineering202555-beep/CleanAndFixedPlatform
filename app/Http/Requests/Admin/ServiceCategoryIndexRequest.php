<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class ServiceCategoryIndexRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'search'         => ['sometimes', 'string', 'max:100'],
            'sort_by'        => ['sometimes', 'in:name,providers_count,requests_count'],
            'sort_direction' => ['sometimes', 'in:asc,desc'],
            'per_page'       => ['sometimes', 'integer', 'min:5', 'max:100'],
        ];
    }
}
