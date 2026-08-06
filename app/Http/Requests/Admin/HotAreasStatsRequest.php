<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class HotAreasStatsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            // null = all-time. رقم = آخر كم يوم (افتراضي 30، موضّح بالـ Service)
            'days'  => ['sometimes', 'integer', 'min:1', 'max:365'],
            'limit' => ['sometimes', 'integer', 'min:1', 'max:100'],
        ];
    }
}
