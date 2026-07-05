<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RegisterRequest extends FormRequest
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

        'firstName' => [
            'required',
            'string',
            'max:50',
        ],

        'lastName' => [
            'required',
            'string',
            'max:50',
        ],

        'email' => [
            'required',
            'email',
            'unique:users,email',
        ],

        'phone' => [
            'required',
            'string',
            'unique:users,phone',
        ],

        'password' => [
            'required',
            'confirmed',
            'min:8',
        ],

    ];
}



public function messages(): array
{
    return [

        'firstName.required' => 'الاسم الأول مطلوب.',

        'lastName.required' => 'اسم العائلة مطلوب.',

        'email.required' => 'البريد الإلكتروني مطلوب.',

        'email.email' => 'صيغة البريد الإلكتروني غير صحيحة.',

        'email.unique' => 'البريد الإلكتروني مستخدم مسبقًا.',

        'phone.required' => 'رقم الهاتف مطلوب.',

        'phone.unique' => 'رقم الهاتف مستخدم مسبقًا.',

        'password.required' => 'كلمة المرور مطلوبة.',

        'password.confirmed' => 'تأكيد كلمة المرور غير مطابق.',

        'password.min' => 'يجب أن تكون كلمة المرور 8 أحرف على الأقل.',

    ];
}

public function attributes(): array
{
    return [

        'firstName' => 'الاسم الأول',

        'lastName' => 'اسم العائلة',

        'email' => 'البريد الإلكتروني',

        'phone' => 'رقم الهاتف',

        'password' => 'كلمة المرور',

    ];
}



}
