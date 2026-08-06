<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\DB;

class StoreCityWithAreasRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            // "إضافة مدينة جديدة" لازم تكون فعلاً جديدة، مش موجودة أصلاً
            'city' => [
                'required',
                'string',
                'max:255',
                function ($attribute, $value, $fail) {
                    $exists = DB::table('service_areas')
                        ->whereRaw('TRIM(city) = ?', [trim($value)])
                        ->exists();

                    if ($exists) {
                        $fail('هذه المدينة موجودة أصلاً، استخدم "إضافة مناطق لمدينة موجودة" بدلاً من ذلك.');
                    }
                },
            ],
            'areas' => ['required', 'array', 'min:1'],
            // distinct بمنع تكرار نفس اسم المنطقة أكتر من مرة بنفس الطلب
            'areas.*' => ['required', 'string', 'max:255', 'distinct'],
        ];
    }
}
