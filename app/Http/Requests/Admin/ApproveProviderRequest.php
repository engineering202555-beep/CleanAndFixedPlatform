<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class ApproveProviderRequest extends FormRequest
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
            'reason' => ['required_if:status,rejected', 'nullable', 'string', 'max:255'],
        ];
    }

    public function messages(): array
    {
        return [
            'reason.required_if' => 'لازم تذكر سبب الرفض.',
        ];
    }
}
