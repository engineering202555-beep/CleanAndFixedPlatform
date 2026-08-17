<?php

namespace App\Http\Requests\ServiceProvider\Profile;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProviderProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'first_name' => [
                'sometimes',
                'string',
                'max:100',
            ],

            'last_name' => [
                'sometimes',
                'string',
                'max:100',
            ],

            'service_category_id' => [
                'sometimes',
                'integer',
                'exists:service_categories,id',
            ],

            'service_area_id' => [
                'sometimes',
                'integer',
                'exists:service_areas,id',
            ],

            'inspection_price' => [
                'sometimes',
                'numeric',
                'min:0',
                'max:999999.99',
            ],

            'bio' => [
                'sometimes',
                'nullable',
                'string',
            ],

            'experience_years' => [
                'sometimes',
                'integer',
                'min:0',
                'max:255',
            ],

            'working_from' => [
                'sometimes',
                'date_format:H:i',
            ],

            'working_to' => [
                'sometimes',
                'date_format:H:i',
            ],

            'latitude' => [
                'sometimes',
                'numeric',
                'between:-90,90',
            ],

            'longitude' => [
                'sometimes',
                'numeric',
                'between:-180,180',
            ],
        ];
    }
}