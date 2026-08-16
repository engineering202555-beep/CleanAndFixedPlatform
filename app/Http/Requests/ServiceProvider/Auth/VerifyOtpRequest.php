<?php

namespace App\Http\Requests\ServiceProvider\Auth;

use Illuminate\Foundation\Http\FormRequest;

class VerifyOtpRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'phone_number' => ['required', 'string', 'exists:users,phone_number'],
            'code'         => ['required', 'string', 'size:6'],
        ];
    }
}
