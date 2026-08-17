<?php

namespace App\Http\Requests\ServiceProvider\ServiceRequest;

use Illuminate\Foundation\Http\FormRequest;

class StoreOfferRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            // nullable لأنه بحالة "عطل غير محدد" السعر بيتحدد بعد
            // الكشف، مش وقت العرض الأول.
            'price' => [
                'nullable',
                'numeric',
                'min:0',
                function ($attribute, $value, $fail) {
                    if (! $this->isDiagnosticPhase() && $value === null) {
                        $fail('السعر إجباري لهذا النوع من العروض.');
                    }
                },
            ],
            'estimated_duration'  => ['required', 'integer', 'min:1'],
            'notes'               => ['nullable', 'string', 'max:500'],
        ];
    }
}
