<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class CustomerFilterRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'area_id'     => ['sometimes', 'integer', 'exists:service_areas,id'],
            'joined_from' => ['sometimes', 'date'],
            'joined_to'   => ['sometimes', 'date', 'after_or_equal:joined_from'],
        ];
    }
}
