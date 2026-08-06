<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class GeographicGrowthStatsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'year'      => ['sometimes', 'integer', 'min:2020', 'max:'.(now()->year + 1)],
            'date_from' => ['sometimes', 'date', 'required_with:date_to'],
            'date_to'   => ['sometimes', 'date', 'after_or_equal:date_from', 'required_with:date_from'],
        ];
    }
}
