<?php

namespace App\Http\Requests\ServiceProvider\Auth;

use Illuminate\Foundation\Http\FormRequest;

class RegisterProviderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'first_name'          => ['required', 'string', 'max:100'],
            'last_name'           => ['required', 'string', 'max:100'],
            'phone_number'        => ['required', 'string', 'unique:users,phone_number'],
            'password'            => ['required', 'string', 'min:8', 'confirmed'],

            'service_category_id' => ['required', 'integer', 'exists:service_categories,id'],
            'service_area_id'     => ['required', 'integer', 'exists:service_areas,id'],
            'inspection_price'    => ['required', 'numeric', 'min:0'],
            'bio'                 => ['nullable', 'string', 'max:1000'],
            'experience_years'    => ['required', 'integer', 'min:0', 'max:60'],

            'working_from'        => ['required', 'date_format:H:i'],
            'working_to'          => ['required', 'date_format:H:i', 'after:working_from'],

            'latitude'            => ['required', 'numeric', 'between:-90,90'],
            'longitude'           => ['required', 'numeric', 'between:-180,180'],

            // أسماء واضحة لكل ملف، بدل documents[] عامة — بس وقت
            // التخزين، id_document وprofession_document كلاهما
            // بيترمّزوا كـ type=documents (نفس الـ enum الحالي بدون تعديل).
            'profile_image' => ['required', 'file', 'mimes:jpg,jpeg,png', 'max:4096'],

            'id_document_front' => ['required', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:4096'],
            'id_document_back'  => ['required', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:4096'],

            'profession_document' => ['required', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:4096'],

        ];
    }
}
