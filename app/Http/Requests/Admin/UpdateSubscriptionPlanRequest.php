<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSubscriptionPlanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'type'               => ['required', 'in:free,paid'],
            'requests_per_month' => ['sometimes', 'integer', 'min:1'],
            'price'              => ['required_if:type,paid', 'nullable', 'numeric', 'min:0'],
            'duration_in_days'   => ['sometimes', 'integer', 'min:1', 'max:365'],
            'description'        => ['nullable', 'string', 'max:1000'],
            'is_active'          => ['sometimes', 'boolean'],
        ];
    }

    protected function prepareForValidation(): void
    {
        if ($this->input('type') === 'free') {
            $this->merge(['price' => 0]);
        }
    }
}
