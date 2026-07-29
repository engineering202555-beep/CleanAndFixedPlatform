<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class ReconsiderProviderRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'status' => ['required', 'in:approved,rejected'],
            // مش required_if هون: لو الأدمن سايبه مرفوض وما بدو يغيّر السبب،
            // بيضل السبب القديم كما هو (شوف reconsider() بالـ Service).
            'reason' => ['nullable', 'string', 'max:255'],
        ];
    }
}
