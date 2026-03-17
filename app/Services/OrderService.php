<?php

namespace App\Services;

use App\Enums\DriverStatus;
use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Models\ActivityLog;
use App\Models\Company;
use App\Models\Coupon;
use App\Models\DeliveryDriver;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderStatusHistory;
use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class OrderService
{
    public function list(Company $company, array $filters = []): LengthAwarePaginator
    {
        $query = Order::forCompany($company->id)
            ->with(['customer', 'driver', 'items'])
            ->when(isset($filters['status']), fn ($q) => $q->where('status', $filters['status']))
            ->when(isset($filters['search']), fn ($q) => $q->where(function ($q) use ($filters) {
                $q->where('code', 'like', "%{$filters['search']}%")
                  ->orWhereHas('customer', fn ($q) => $q->where('name', 'like', "%{$filters['search']}%"));
            }))
            ->when(isset($filters['date_from']), fn ($q) => $q->whereDate('created_at', '>=', $filters['date_from']))
            ->when(isset($filters['date_to']), fn ($q) => $q->whereDate('created_at', '<=', $filters['date_to']))
            ->orderBy('created_at', 'desc');

        return $query->paginate($filters['per_page'] ?? 20);
    }

    public function create(Company $company, User $user, array $data): Order
    {
        return DB::transaction(function () use ($company, $user, $data) {
            $coupon   = null;
            $subtotal = 0;

            // Build items total
            $items = $data['items'] ?? [];
            foreach ($items as &$item) {
                $itemTotal = $item['unit_price'] * $item['quantity'];
                foreach ($item['options'] ?? [] as $option) {
                    $itemTotal += $option['additional_price'] * $item['quantity'];
                }
                $item['total'] = $itemTotal;
                $subtotal += $itemTotal;
            }
            unset($item);

            // Coupon discount
            $discountAmount = 0;
            if (isset($data['coupon_code'])) {
                $coupon = Coupon::forCompany($company->id)
                    ->where('code', $data['coupon_code'])
                    ->first();

                if (! $coupon || ! $coupon->isValid($subtotal)) {
                    throw ValidationException::withMessages(['coupon_code' => 'Invalid or expired coupon.']);
                }
                $discountAmount = $coupon->calculateDiscount($subtotal);
            }

            $deliveryFee = $data['delivery_fee'] ?? $company->default_delivery_fee;
            $total       = max(0, $subtotal - $discountAmount + $deliveryFee);

            $order = $company->orders()->create([
                'customer_id'         => $data['customer_id'],
                'customer_address_id' => $data['customer_address_id'] ?? null,
                'coupon_id'           => $coupon?->id,
                'code'                => $this->generateCode($company->id),
                'order_type'          => $data['order_type'],
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

            // Create order items
            foreach ($items as $itemData) {
                $options = $itemData['options'] ?? [];
                unset($itemData['options']);

                $orderItem = $order->items()->create($itemData);

                foreach ($options as $optionData) {
                    $orderItem->options()->create($optionData);
                }
            }

            // Record initial status history
            $this->recordStatusHistory($order, OrderStatus::Pending, $user);

            // Increment coupon uses
            if ($coupon) {
                $coupon->increment('uses_count');
            }

            ActivityLog::record('order.created', $order, "Order #{$order->code} created", ['total' => $total]);

            return $order->load(['customer', 'items.options', 'address']);
        });
    }

    public function updateStatus(Order $order, OrderStatus $newStatus, User $user, ?string $note = null): Order
    {
        if ($order->isCanceled()) {
            throw ValidationException::withMessages(['status' => 'Cannot modify a canceled order.']);
        }

        if (! $order->canTransitionTo($newStatus)) {
            throw ValidationException::withMessages(['status' => "Cannot transition from {$order->status->value} to {$newStatus->value}."]);
        }

        DB::transaction(function () use ($order, $newStatus, $user, $note) {
            $timestamps = match ($newStatus) {
                OrderStatus::Confirmed      => ['confirmed_at' => now()],
                OrderStatus::OutForDelivery => ['dispatched_at' => now()],
                OrderStatus::Delivered      => ['delivered_at' => now()],
                OrderStatus::Canceled       => ['canceled_at' => now()],
                default                     => [],
            };

            $order->update(array_merge(['status' => $newStatus->value], $timestamps));
            $this->recordStatusHistory($order, $newStatus, $user, $note);

            ActivityLog::record("order.status_changed", $order, "Order #{$order->code} status changed to {$newStatus->value}");
        });

        return $order->fresh();
    }

    public function assignDriver(Order $order, DeliveryDriver $driver, User $user): Order
    {
        if (! $driver->isAvailable()) {
            throw ValidationException::withMessages(['driver_id' => 'Driver is not available.']);
        }

        DB::transaction(function () use ($order, $driver, $user) {
            // Free previous driver if any
            if ($order->delivery_driver_id && $order->delivery_driver_id !== $driver->id) {
                DeliveryDriver::find($order->delivery_driver_id)?->update(['status' => DriverStatus::Available->value]);
            }

            $order->update(['delivery_driver_id' => $driver->id]);
            $driver->update(['status' => DriverStatus::Busy->value]);

            ActivityLog::record('order.driver_assigned', $order, "Driver {$driver->name} assigned to order #{$order->code}");
        });

        return $order->fresh()->load('driver');
    }

    public function cancel(Order $order, User $user, ?string $reason = null): Order
    {
        return $this->updateStatus($order, OrderStatus::Canceled, $user, $reason);
    }

    private function generateCode(int $companyId): string
    {
        $prefix = str_pad($companyId, 3, '0', STR_PAD_LEFT);
        $count  = Order::where('company_id', $companyId)->withTrashed()->count() + 1;
        return $prefix . str_pad($count, 6, '0', STR_PAD_LEFT);
    }

    private function recordStatusHistory(Order $order, OrderStatus $status, User $user, ?string $note = null): void
    {
        OrderStatusHistory::create([
            'order_id'   => $order->id,
            'changed_by' => $user->id,
            'status'     => $status->value,
            'note'       => $note,
            'created_at' => now(),
        ]);
    }
}
