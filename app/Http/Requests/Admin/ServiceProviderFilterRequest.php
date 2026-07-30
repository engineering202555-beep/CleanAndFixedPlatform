<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class ServiceProviderFilterRequest extends FormRequest
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
            'category_id'         => ['sometimes', 'integer', 'exists:service_categories,id'],
            'area_id'              => ['sometimes', 'integer', 'exists:service_areas,id'],
            'subscription_id'      => ['sometimes', 'integer', 'exists:subscriptions,id'],
            'search'               => ['sometimes', 'string', 'max:100'],
            'sort_by'              => ['sometimes', 'in:rating,experience_years,inspection_price,created_at'],
            'sort_direction'       => ['sometimes', 'in:asc,desc'],
            'per_page'             => ['sometimes', 'integer', 'min:5', 'max:100'],
        ];
    }

    public function messages(): array
    {
        return [
            'category_id.exists' => 'تصنيف الخدمة المحدد غير موجود.',
            'area_id.exists'     => 'النطاق الجغرافي المحدد غير موجود.',
        ];
    }
}
