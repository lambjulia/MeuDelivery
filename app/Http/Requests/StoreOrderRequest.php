<?php

namespace App\Http\Requests;

use App\Enums\OrderType;
use App\Enums\PaymentMethod;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->company_id !== null;
    }

    public function rules(): array
    {
        return [
            'customer_id'          => ['required', 'integer', 'exists:customers,id'],
            'customer_address_id'  => ['nullable', 'integer', 'exists:customer_addresses,id'],
            'order_type'           => ['required', Rule::enum(OrderType::class)],
            'payment_method'       => ['required', Rule::enum(PaymentMethod::class)],
            'coupon_code'          => ['nullable', 'string'],
            'delivery_fee'         => ['nullable', 'numeric', 'min:0'],
            'notes'                => ['nullable', 'string', 'max:500'],
            'change_for'           => ['nullable', 'numeric', 'min:0'],
            'items'                => ['required', 'array', 'min:1'],
            'items.*.product_id'   => ['required', 'integer', 'exists:products,id'],
            'items.*.product_name' => ['required', 'string'],
            'items.*.unit_price'   => ['required', 'numeric', 'min:0'],
            'items.*.quantity'     => ['required', 'integer', 'min:1'],
            'items.*.notes'        => ['nullable', 'string', 'max:200'],
            'items.*.options'                          => ['nullable', 'array'],
            'items.*.options.*.product_option_id'      => ['nullable', 'integer'],
            'items.*.options.*.product_option_name'    => ['required', 'string'],
            'items.*.options.*.product_option_group_name' => ['required', 'string'],
            'items.*.options.*.additional_price'       => ['numeric', 'min:0'],
        ];
    }
}
