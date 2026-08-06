<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class PriceComparisonRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'service_category_id' => ['sometimes', 'integer', 'exists:service_categories,id'],
            'category_ids'        => ['sometimes', 'array', 'min:2'],
            'category_ids.*'      => ['integer', 'exists:service_categories,id'],
            'request_type'        => ['sometimes', 'in:specific_fault,unspecified_fault'],
            'area_id'             => ['sometimes', 'integer', 'exists:service_areas,id'],
            'area_ids'            => ['sometimes', 'array', 'min:2'],
            'area_ids.*'          => ['integer', 'exists:service_areas,id'],
            'date_from'           => ['sometimes', 'date', 'required_with:date_to'],
            'date_to'             => ['sometimes', 'date', 'after_or_equal:date_from', 'required_with:date_from'],
        ];
    }
}
