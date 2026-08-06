<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\DB;

class AddAreasToCityRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            // "city" لازم تكون من القائمة المنسدلة (قيمة موجودة فعلاً)،
            // مش نص جديد يكتبه الأدمن يدوياً.
            'city' => [
                'required',
                'string',
                function ($attribute, $value, $fail) {
                    $exists = DB::table('service_areas')
                        ->whereRaw('TRIM(city) = ?', [trim($value)])
                        ->exists();

                    if (! $exists) {
                        $fail('هذه المدينة غير موجودة، استخدم "إضافة مدينة جديدة" بدلاً من ذلك.');
                    }
                },
            ],
            'areas' => ['required', 'array', 'min:1'],
            'areas.*' => [
                'required',
                'string',
                'max:255',
                'distinct',
                function ($attribute, $value, $fail) {
                    $city = trim($this->input('city', ''));

                    $alreadyExists = DB::table('service_areas')
                        ->whereRaw('TRIM(city) = ?', [$city])
                        ->whereRaw('TRIM(area_name) = ?', [trim($value)])
                        ->exists();

                    if ($alreadyExists) {
                        $fail("منطقة \"{$value}\" موجودة أصلاً بهذه المدينة.");
                    }
                },
            ],
        ];
    }
}
