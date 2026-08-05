<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CustomerBlockRequest extends FormRequest
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
        $isFirstTimeBlock = $this->route('customer')?->status !== 'blocked';

        return [
            'reason' => [Rule::requiredIf($isFirstTimeBlock), 'string', 'max:255'],
            'duration_in_days' => ['required', 'integer', 'min:1', 'max:365'],
        ];
    }
}
