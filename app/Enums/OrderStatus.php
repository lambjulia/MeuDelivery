<?php

namespace App\Enums;

enum OrderStatus: string
{
    case Pending        = 'pending';
    case Confirmed      = 'confirmed';
    case Preparing      = 'preparing';
    case Ready          = 'ready';
    case OutForDelivery = 'out_for_delivery';
    case Delivered      = 'delivered';
    case Canceled       = 'canceled';

    public function label(): string
    {
        return match($this) {
            self::Pending        => 'Pending',
            self::Confirmed      => 'Confirmed',
            self::Preparing      => 'Preparing',
            self::Ready          => 'Ready',
            self::OutForDelivery => 'Out for Delivery',
            self::Delivered      => 'Delivered',
            self::Canceled       => 'Canceled',
        };
    }

    public function color(): string
    {
        return match($this) {
            self::Pending        => 'warn',
            self::Confirmed      => 'info',
            self::Preparing      => 'info',
            self::Ready          => 'success',
            self::OutForDelivery => 'primary',
            self::Delivered      => 'success',
            self::Canceled       => 'danger',
        };
    }

    public function isFinal(): bool
    {
        return in_array($this, [self::Delivered, self::Canceled]);
    }

    public function validNextStatuses(): array
    {
        return match($this) {
            self::Pending        => [self::Confirmed, self::Canceled],
            self::Confirmed      => [self::Preparing, self::Canceled],
            self::Preparing      => [self::Ready, self::Canceled],
            self::Ready          => [self::OutForDelivery, self::Delivered, self::Canceled],
            self::OutForDelivery => [self::Delivered, self::Canceled],
            self::Delivered      => [],
            self::Canceled       => [],
        };
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
