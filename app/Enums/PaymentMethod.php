<?php

namespace App\Enums;

enum PaymentMethod: string
{
    case Cash       = 'cash';
    case CreditCard = 'credit_card';
    case DebitCard  = 'debit_card';
    case Pix        = 'pix';
    case Voucher    = 'voucher';

    public function label(): string
    {
        return match($this) {
            self::Cash       => 'Cash',
            self::CreditCard => 'Credit Card',
            self::DebitCard  => 'Debit Card',
            self::Pix        => 'PIX',
            self::Voucher    => 'Voucher',
        };
    }

    public function requiresChange(): bool
    {
        return $this === self::Cash;
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
