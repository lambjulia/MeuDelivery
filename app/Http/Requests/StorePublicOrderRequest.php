<?php

namespace App\Http\Requests;

use App\Enums\OrderType;
use App\Enums\PaymentMethod;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePublicOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            // Customer
            'customer.name'  => ['required', 'string', 'max:150'],
            'customer.phone' => ['required', 'string', 'max:20'],
            'customer.email' => ['nullable', 'email', 'max:150'],

            // Order
            'order_type'     => ['required', Rule::in(OrderType::values())],
            'payment_method' => ['required', Rule::in(PaymentMethod::values())],
            'notes'          => ['nullable', 'string', 'max:500'],
            'coupon_code'    => ['nullable', 'string', 'max:50'],
            'change_for'     => ['nullable', 'numeric', 'min:0'],

            // Items
            'items'                     => ['required', 'array', 'min:1'],
            'items.*.product_id'        => ['required', 'integer'],
            'items.*.quantity'          => ['required', 'integer', 'min:1'],
            'items.*.notes'             => ['nullable', 'string', 'max:200'],
            'items.*.options'           => ['nullable', 'array'],
            'items.*.options.*.option_id' => ['required', 'integer'],

            // Address (only for delivery)
            'address.street'     => ['required_if:order_type,delivery', 'nullable', 'string'],
            'address.number'     => ['required_if:order_type,delivery', 'nullable', 'string'],
            'address.complement' => ['nullable', 'string'],
            'address.district'   => ['required_if:order_type,delivery', 'nullable', 'string'],
            'address.city'       => ['required_if:order_type,delivery', 'nullable', 'string'],
            'address.state'      => ['nullable', 'string', 'max:2'],
            'address.zip_code'   => ['nullable', 'string', 'max:10'],
            'address.reference'  => ['nullable', 'string', 'max:200'],
        ];
    }

    public function messages(): array
    {
        return [
            'customer.name.required'  => 'Your name is required.',
            'customer.phone.required' => 'Your phone number is required.',
            'order_type.required'     => 'Please select delivery or pickup.',
            'payment_method.required' => 'Please select a payment method.',
            'items.required'          => 'Your cart is empty.',
            'items.min'               => 'Your cart is empty.',
            'address.street.required_if'   => 'Street address is required for delivery.',
            'address.number.required_if'   => 'Address number is required for delivery.',
            'address.district.required_if' => 'Neighborhood is required for delivery.',
            'address.city.required_if'     => 'City is required for delivery.',
        ];
    }
}
