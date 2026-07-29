<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class GrantComplimentaryMonthRequest extends FormRequest
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
            'subscription_id' => [
                'required',
                'integer',
                // القيمة لازم تشاور على خطة type=paid تحديداً، مش أي
                // خطة موجودة بالجدول (منع منح خطة مجانية "كمجانية"!).
                Rule::exists('subscriptions', 'id')
                    ->where('type', 'paid')
                    ->where('is_active', 1),            ],
        ];
    }

    public function messages(): array
    {
        return [
            'subscription_id.exists' => 'الخطة المحددة غير موجودة أو ليست خطة مدفوعة.',
        ];
    }
}
