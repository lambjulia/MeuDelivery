<?php

namespace App\Actions\Store;

use App\Enums\OrderStatus;
use App\Enums\OrderType;
use App\Enums\PaymentStatus;
use App\Models\Company;
use App\Models\Coupon;
use App\Models\Customer;
use App\Models\CustomerAddress;
use App\Models\Order;
use App\Models\OrderStatusHistory;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CreatePublicOrderAction
{
    public function __construct(
        private readonly CalculateCartTotalsAction  $calculateTotals,
        private readonly CalculateDeliveryFeeAction $calculateFee,
        private readonly ValidateCouponAction       $validateCoupon,
    ) {}

    public function execute(Company $company, array $data): Order
    {
        return DB::transaction(function () use ($company, $data) {
            // 1. Calculate/verify cart totals server-side
            ['items' => $items, 'subtotal' => $subtotal] = $this->calculateTotals->execute(
                $data['items'],
                $company->id
            );

            // 2. Coupon
            $coupon         = null;
            $discountAmount = 0;
            if (! empty($data['coupon_code'])) {
                $result = $this->validateCoupon->execute($company->id, $data['coupon_code'], $subtotal);
                if (! $result['valid']) {
                    throw new \InvalidArgumentException($result['message']);
                }
                $coupon         = Coupon::find($result['coupon']['id']);
                $discountAmount = $result['coupon']['discount'];
            }

            // 3. Delivery fee
            $orderType   = OrderType::from($data['order_type']);
            $deliveryFee = 0.0;

            if ($orderType === OrderType::Delivery) {
                if (isset($data['delivery_fee'])) {
                    $deliveryFee = (float) $data['delivery_fee'];
                } elseif (isset($data['address']['district'])) {
                    $feeResult   = $this->calculateFee->execute(
                        $company->id,
                        $data['address']['district'] ?? null,
                        $data['address']['city'] ?? null,
                    );
                    $deliveryFee = $feeResult['fee'] ?? (float) $company->default_delivery_fee;
                } else {
                    $deliveryFee = (float) $company->default_delivery_fee;
                }
            }

            // 4. Min order validation
            if ($company->min_order_amount > 0 && $subtotal < $company->min_order_amount) {
                throw new \InvalidArgumentException(
                    "Minimum order amount is R$ {$company->min_order_amount}."
                );
            }

            $total = max(0, $subtotal - $discountAmount + $deliveryFee);

            // 5. Resolve or create customer
            $customer = $this->resolveCustomer($company->id, $data['customer']);

            // 6. Resolve or create address for delivery orders
            $addressId = null;
            if ($orderType === OrderType::Delivery && ! empty($data['address'])) {
                $address = CustomerAddress::create([
                    'customer_id' => $customer->id,
                    'company_id'  => $company->id,
                    'label'       => 'Pedido #',
                    'zip_code'    => $data['address']['zip_code'] ?? null,
                    'street'      => $data['address']['street'],
                    'number'      => $data['address']['number'],
                    'complement'  => $data['address']['complement'] ?? null,
                    'district'    => $data['address']['district'],
                    'city'        => $data['address']['city'],
                    'state'       => $data['address']['state'] ?? null,
                    'reference'   => $data['address']['reference'] ?? null,
                    'is_default'  => false,
                ]);
                $addressId = $address->id;
            }

            // 7. Create order
            $order = $company->orders()->create([
                'customer_id'         => $customer->id,
                'customer_address_id' => $addressId,
                'coupon_id'           => $coupon?->id,
                'code'                => $this->generateCode(),
                'order_type'          => $orderType->value,
                'status'              => OrderStatus::Pending->value,
                'subtotal'            => $subtotal,
                'discount_amount'     => $discountAmount,
                'delivery_fee'        => $deliveryFee,
                'total'               => $total,
                'payment_method'      => $data['payment_method'],
                'payment_status'      => PaymentStatus::Pending->value,
                'notes'               => $data['notes'] ?? null,
                'change_for'          => $data['change_for'] ?? null,
            ]);

            // 8. Create items
            foreach ($items as $itemData) {
                $options = $itemData['options'];
                unset($itemData['options']);
                $orderItem = $order->items()->create($itemData);
                foreach ($options as $optionData) {
                    $orderItem->options()->create($optionData);
                }
            }

            // 9. Initial status history
            OrderStatusHistory::create([
                'order_id'   => $order->id,
                'changed_by' => null,
                'status'     => OrderStatus::Pending->value,
                'note'       => 'Order placed by customer',
                'created_at' => now(),
            ]);

            // 10. Increment coupon
            $coupon?->increment('uses_count');

            return $order->load(['items.options', 'address', 'statusHistory']);
        });
    }

    private function resolveCustomer(int $companyId, array $data): Customer
    {
        // Try to find by phone within this company
        $customer = Customer::where('company_id', $companyId)
            ->where('phone', $data['phone'])
            ->first();

        if ($customer) {
            // Update name/email if provided
            $customer->update(array_filter([
                'name'  => $data['name'],
                'email' => $data['email'] ?? $customer->email,
            ]));
            return $customer;
        }

        return Customer::create([
            'company_id' => $companyId,
            'name'       => $data['name'],
            'phone'      => $data['phone'],
            'email'      => $data['email'] ?? null,
            'is_active'  => true,
        ]);
    }

    private function generateCode(): string
    {
        do {
            $code = strtoupper(Str::random(8));
        } while (Order::where('code', $code)->exists());

        return $code;
    }
}
