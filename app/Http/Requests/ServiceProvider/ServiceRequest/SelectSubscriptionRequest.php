<?php

namespace App\Http\Requests\ServiceProvider\ServiceRequest;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SelectSubscriptionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'subscription_id' => [
                'required',
                'integer',
                // بس خطط مدفوعة ومفعّلة — الخطة المجانية ما بتحتاج
                // "طلب وموافقة أدمن" أصلاً، هي بترجع تلقائياً عبر
                // endpoint الإلغاء.
                Rule::exists('subscriptions', 'id')
                    ->where('type', 'paid')
                    ->where('is_active', true),
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'subscription_id.exists' => 'الخطة المحددة غير موجودة، أو ليست مدفوعة، أو غير مفعّلة حالياً.',
        ];
    }
}
