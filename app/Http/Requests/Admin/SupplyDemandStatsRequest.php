<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class SupplyDemandStatsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'days'        => ['sometimes', 'integer', 'min:1', 'max:365'],
            'category_id' => ['sometimes', 'integer', 'exists:service_categories,id'],
        ];
    }
}
