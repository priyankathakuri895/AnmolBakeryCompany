<?php

namespace App\Enums;

enum ExpenseCategory: string
{
    case Fuel = 'fuel';
    case Maintenance = 'maintenance';
    case Salary = 'salary';
    case Rent = 'rent';
    case Utilities = 'utilities';
    case Wastage = 'wastage';
    case Other = 'other';

    public function label(): string
    {
        return match ($this) {
            self::Fuel => 'Fuel',
            self::Maintenance => 'Vehicle maintenance',
            self::Salary => 'Salary / wages',
            self::Rent => 'Rent',
            self::Utilities => 'Utilities',
            self::Wastage => 'Wastage / loss',
            self::Other => 'Other',
        };
    }
}
