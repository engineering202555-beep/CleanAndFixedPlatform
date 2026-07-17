<?php

namespace App\Http\Requests\Customer;

use Illuminate\Foundation\Http\FormRequest;

class ResetPasswordRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }
    
    public function rules(): array
    {
        return [

            'phone_number' => ['required', 'string'],

            'code' => ['required', 'digits:6'],

            'password' => [
                'required',
                'confirmed',
                'min:8'
            ]

        ];
    }
}