<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\DB;

class UpdateCityNameRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $currentCity = trim((string) $this->route('city'));

        return [
            'new_name' => [
                'required',
                'string',
                'max:255',
                function ($attribute, $value, $fail) use ($currentCity) {
                    $value = trim($value);

                    if ($value === $currentCity) {
                        $fail('الاسم الجديد مطابق للاسم الحالي.');

                        return;
                    }

                    // منع الدمج العرضي بين مدينتين مختلفتين تحت اسم واحد
                    $alreadyExists = DB::table('service_areas')
                        ->whereRaw('TRIM(city) = ?', [$value])
                        ->exists();

                    if ($alreadyExists) {
                        $fail('يوجد مدينة أخرى بهذا الاسم فعلاً، لا يمكن الدمج التلقائي بهذه الطريقة.');
                    }
                },
            ],
        ];
    }
}
