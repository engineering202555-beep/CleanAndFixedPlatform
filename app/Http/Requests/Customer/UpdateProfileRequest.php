<?php

namespace App\Http\Requests\Customer;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'first_name' => ['sometimes', 'string', 'max:255'],
           'last_name' => ['sometimes', 'string', 'max:255'],

           

            'service_area_id' => [
                'sometimes',
                'exists:service_areas,id',
            ],
        ];
    }
}