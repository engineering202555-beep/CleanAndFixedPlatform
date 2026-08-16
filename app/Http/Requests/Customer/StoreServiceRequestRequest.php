<?php

namespace App\Http\Requests\Customer;

use Illuminate\Foundation\Http\FormRequest;

class StoreServiceRequestRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

   public function rules(): array
{
    return [

        'service_category_id' => [
            'required',
            'exists:service_categories,id'
        ],
'service_area_id' => [
            'required',
            'exists:service_areas,id'
        ],
        

        'request_type' => [
            'required',
            'in:specific_fault,unspecified_fault'
        ],

        'description' => [
            'nullable',
            'string',
            'max:1000'
        ],

       'latitude_x' => [
                'required',
                'numeric',
                'between:-90,90',
            ],

            'longitude_y' => [
                'required',
                'numeric',
                'between:-180,180',
            ],

        'is_urgent' => [
            'required',
            'boolean'
        ],

        'starts_at' => [
            'nullable',
            'date',
            'after:now'
        ],

        'duration_in_minutes' => [
            'nullable',
            'integer',
            'min:15'
        ],

        'images' => [
            'nullable',
            'array',
            'max:5'
        ],

        'images.*' => [
            'image',
            'mimes:jpg,jpg,png,jpeg',
            'max:4096'
        ],

    ];
}

public function withValidator($validator)
{
    $validator->after(function ($validator) {

        if (
            $this->request_type == 'specific_fault' &&
            empty($this->description) &&
            !$this->hasFile('images')
        ) {

            $validator->errors()->add(
                'description',
                'يجب كتابة وصف أو رفع صورة واحدة على الأقل.'
            );

        }

        if (
            !$this->boolean('is_urgent') &&
            empty($this->starts_at)
        ) {

            $validator->errors()->add(
                'starts_at',
                'يجب تحديد موعد التنفيذ.'
            );

        }

    });
}
}