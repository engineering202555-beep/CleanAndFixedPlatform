<?php

namespace App\Http\Requests\ServiceProvider\Auth;

use Illuminate\Foundation\Http\FormRequest;

class LoginProviderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'phone_number' => ['required', 'string'],
            'password'     => ['required', 'string'],
        ];
    }
}
