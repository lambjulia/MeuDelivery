<?php

namespace App\Enums;

enum DriverStatus: string
{
    case Available = 'available';
    case Busy      = 'busy';
    case Offline   = 'offline';

    public function label(): string
    {
        return match($this) {
            self::Available => 'Available',
            self::Busy      => 'Busy',
            self::Offline   => 'Offline',
        };
    }

    public function color(): string
    {
        return match($this) {
            self::Available => 'success',
            self::Busy      => 'warn',
            self::Offline   => 'secondary',
        };
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
