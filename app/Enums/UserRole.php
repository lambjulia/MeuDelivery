<?php

namespace App\Enums;

enum UserRole: string
{
    case Owner      = 'owner';
    case Manager    = 'manager';
    case Attendant  = 'attendant';
    case Dispatcher = 'dispatcher';
    case Driver     = 'driver';

    public function label(): string
    {
        return match($this) {
            self::Owner      => 'Owner',
            self::Manager    => 'Manager',
            self::Attendant  => 'Attendant',
            self::Dispatcher => 'Dispatcher',
            self::Driver     => 'Driver',
        };
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
