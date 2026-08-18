<?php

namespace App\Http\Requests\Customer;

use Illuminate\Foundation\Http\FormRequest;

class SaveFcmTokenRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'fcm_token' => [
                'required',
                'string',
                'max:1000',
            ],
        ];
    }
 public function messages(): array
    {
        return [
            'fcm_token.required' => 'FCM token is required.',
            'fcm_token.string' => 'FCM token must be a string.',
            'fcm_token.max' => 'FCM token is too long.',
        ];
    }

}