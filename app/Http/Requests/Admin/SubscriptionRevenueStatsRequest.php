<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class SubscriptionRevenueStatsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'year'            => ['sometimes', 'integer', 'min:2020', 'max:'.(now()->year + 1)],
            'date_from'       => ['sometimes', 'date', 'required_with:date_to'],
            'date_to'         => ['sometimes', 'date', 'after_or_equal:date_from', 'required_with:date_from'],
            'subscription_id' => ['sometimes', 'integer', 'exists:subscriptions,id'],
            // فلتر status هون اختياري وإضافي فوق شرط الأهلية للإيراد
            // (مش بديل عنه) — مثلاً "بس الإيراد يلي لسا active حالياً".
            'status'          => ['sometimes', 'in:active,cancelled'],
        ];
    }
}
