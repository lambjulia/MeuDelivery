<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreCustomerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->company_id !== null;
    }

    public function rules(): array
    {
        return [
            'name'     => ['required', 'string', 'max:150'],
            'phone'    => ['required', 'string', 'max:20'],
            'email'    => ['nullable', 'email', 'max:150'],
            'document' => ['nullable', 'string', 'max:20'],
            'notes'    => ['nullable', 'string', 'max:500'],
            'addresses'                => ['nullable', 'array'],
            'addresses.*.street'       => ['required', 'string'],
            'addresses.*.number'       => ['required', 'string'],
            'addresses.*.district'     => ['required', 'string'],
            'addresses.*.city'         => ['required', 'string'],
            'addresses.*.state'        => ['required', 'string', 'size:2'],
            'addresses.*.zip_code'     => ['nullable', 'string', 'max:10'],
            'addresses.*.complement'   => ['nullable', 'string'],
            'addresses.*.reference'    => ['nullable', 'string'],
        ];
    }
}
