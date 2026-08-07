<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class MostRequestedCategoriesStatsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'date_from' => ['sometimes', 'date', 'required_with:date_to'],
            'date_to'   => ['sometimes', 'date', 'after_or_equal:date_from', 'required_with:date_from'],
            'city'      => ['sometimes', 'string', 'max:255'],
            'area_id'   => ['sometimes', 'integer', 'exists:service_areas,id'],
        ];
    }
}
