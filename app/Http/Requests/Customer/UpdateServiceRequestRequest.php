<?php

namespace App\Http\Requests\Customer;

use Illuminate\Foundation\Http\FormRequest;

class UpdateServiceRequestRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'service_category_id' => [
                'sometimes',
                'integer',
                'exists:service_categories,id',
            ],

  'service_area_id' => [
                'sometimes',
                'integer',
                'exists:service_area_id,id',
            ],



            'request_type' => [
                'sometimes',
                'in:specific_fault,unspecified_fault',
            ],

            'description' => [
                'sometimes',
                'nullable',
                'string',
            ],

            'starts_at' => [
                'sometimes',
                'date',
            ],

            'latitude_x' => [
                'sometimes',
                'numeric',
                'between:-90,90',
            ],

            'longitude_y' => [
                'sometimes',
                'numeric',
                'between:-180,180',
            ],

            'duration_in_minutes' => [
                'sometimes',
                'integer',
                'min:1',
            ],
        ];
    }
}