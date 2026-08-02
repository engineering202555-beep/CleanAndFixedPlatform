<?php

namespace App\Http\Requests\Customer;

use Illuminate\Foundation\Http\FormRequest;

class StoreReviewRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [

            'service_request_id' => [
                'required',
                'exists:service_requests,id'
            ],

            'provider_rating' => [
                'required',
                'integer',
                'between:1,5'
            ],

            'comment' => [
                'nullable',
                'string',
                'max:1000'
            ],

        ];
    }
}