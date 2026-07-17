<?php

namespace App\Http\Requests\Customer;

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

        'first_name' => [
            'required',
            'string',
            'max:50',
        ],

        'last_name' => [
            'required',
            'string',
            'max:50',
        ],

        

        'phone_number' => [
            'required',
            'string',
            'unique:users,phone_number',
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

        'first_name.required' => 'الاسم الأول مطلوب.',

        'last_name.required' => 'اسم العائلة مطلوب.',



        'phone_number.required' => 'رقم الهاتف مطلوب.',

        'phone_number.unique' => 'رقم الهاتف مستخدم مسبقًا.',

        'password.required' => 'كلمة المرور مطلوبة.',

        'password.confirmed' => 'تأكيد كلمة المرور غير مطابق.',

        'password.min' => 'يجب أن تكون كلمة المرور 8 أحرف على الأقل.',

    ];
}

public function attributes(): array
{
    return [

        'first_name' => 'الاسم الأول',

        'last_name' => 'اسم العائلة',

        

        'phone_number' => 'رقم الهاتف',

        'password' => 'كلمة المرور',

    ];
}



}
