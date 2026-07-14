<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class LoginRequest extends FormRequest
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

            'user_name' => ['required', 'string'],

            'password' => [
                'required',
                'string',
                'min:8',
                'regex:/[A-Za-z]/', // يجب أن يحتوي على حرف واحد على الأقل
            ],
        ];
    }
}
