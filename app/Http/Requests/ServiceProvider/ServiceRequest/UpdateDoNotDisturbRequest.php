<?php
namespace App\Http\Requests\ServiceProvider\ServiceRequest;

use Illuminate\Foundation\Http\FormRequest;

class UpdateDoNotDisturbRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'do_not_disturb' => ['required', 'boolean'],
        ];
    }
}
