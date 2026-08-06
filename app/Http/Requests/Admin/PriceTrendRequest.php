<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class PriceTrendRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'service_category_id' => ['sometimes', 'integer', 'exists:service_categories,id'],
            'area_id'              => ['sometimes', 'integer', 'exists:service_areas,id'],
            'year'                 => ['sometimes', 'integer', 'min:2020', 'max:'.(now()->year + 1)],
            'date_from'            => ['sometimes', 'date', 'required_with:date_to'],
            'date_to'              => ['sometimes', 'date', 'after_or_equal:date_from', 'required_with:date_from'],
        ];
    }

    /**
     * لازم فلتر تضييق واحد على الأقل (منطقة أو تصنيف)، وإلا
     * الاستعلام بيرجع Line Chart بمئات الخطوط المتراكبة بلا معنى.
     */
    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            if (! $this->filled('service_category_id') && ! $this->filled('area_id')) {
                $validator->errors()->add(
                    'service_category_id',
                    'لازم تحدد منطقة أو تصنيف خدمة واحد على الأقل لعرض خط الاتجاه الشهري.'
                );
            }
        });
    }
}
