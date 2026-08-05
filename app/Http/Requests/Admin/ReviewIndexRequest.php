<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class ReviewIndexRequest extends FormRequest
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
            'provider_search' => ['sometimes', 'string', 'max:100'],
            'category_id'     => ['sometimes', 'integer', 'exists:service_categories,id'],
            'sort_by'         => ['sometimes', 'in:latest,highest_rated'],
            'per_page'        => ['sometimes', 'integer', 'min:5', 'max:100'],
        ];
    }
}
