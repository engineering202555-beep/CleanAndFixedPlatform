<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class AreaDensityStatsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'service_area_id' => ['sometimes', 'integer', 'exists:service_areas,id'],
            'days'            => ['sometimes', 'integer', 'min:1', 'max:365'],
            // عدد المنازل العشرية للتقريب (Grid Cell size)، 3 ≈ 110م، 4 ≈ 11م
            'precision'       => ['sometimes', 'integer', 'min:2', 'max:5'],
        ];
    }
}
