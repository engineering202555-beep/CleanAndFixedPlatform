<?php

namespace App\Http\Requests\Admin;

use App\Models\ServiceArea;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\DB;

class UpdateAreaNameRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        /** @var ServiceArea $area */
        $area = $this->route('serviceArea');

        return [
            'area_name' => [
                'required',
                'string',
                'max:255',
                function ($attribute, $value, $fail) use ($area) {
                    $value = trim($value);

                    $duplicate = DB::table('service_areas')
                        ->whereRaw('TRIM(city) = ?', [trim($area->city)])
                        ->whereRaw('TRIM(area_name) = ?', [$value])
                        ->where('id', '!=', $area->id)
                        ->exists();

                    if ($duplicate) {
                        $fail("منطقة \"{$value}\" موجودة أصلاً بهذه المدينة.");
                    }
                },
            ],
        ];
    }
}
