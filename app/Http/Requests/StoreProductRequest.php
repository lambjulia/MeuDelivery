<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->canManage();
    }

    public function rules(): array
    {
        return [
            'category_id'              => ['nullable', 'exists:categories,id'],
            'name'                     => ['required', 'string', 'max:150'],
            'description'              => ['nullable', 'string', 'max:1000'],
            'image'                    => ['nullable', 'image', 'max:4096'],
            'base_price'               => ['required', 'numeric', 'min:0.01'],
            'promotional_price'        => ['nullable', 'numeric', 'min:0.01', 'lt:base_price'],
            'sku'                      => ['nullable', 'string', 'max:50'],
            'is_active'                => ['boolean'],
            'preparation_time'         => ['nullable', 'integer', 'min:1', 'max:180'],
            'sort_order'               => ['nullable', 'integer', 'min:0'],
            'option_groups'            => ['nullable', 'array'],
            'option_groups.*.name'     => ['required', 'string', 'max:100'],
            'option_groups.*.is_required'    => ['boolean'],
            'option_groups.*.min_selections' => ['integer', 'min:0'],
            'option_groups.*.max_selections' => ['integer', 'min:1'],
            'option_groups.*.is_multiple'    => ['boolean'],
            'option_groups.*.options'              => ['required', 'array', 'min:1'],
            'option_groups.*.options.*.name'       => ['required', 'string', 'max:100'],
            'option_groups.*.options.*.additional_price' => ['numeric', 'min:0'],
        ];
    }
}
