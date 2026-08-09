<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class SubscriptionProviderIndexRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'status'        => ['sometimes', 'in:pending_payment,active,cancelled'],
            'subscription_id' => ['sometimes', 'integer', 'exists:subscriptions,id'],
            'provider_search' => ['sometimes', 'string', 'max:100'],
            'per_page'      => ['sometimes', 'integer', 'min:5', 'max:100'],
        ];
    }
}
