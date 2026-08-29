<?php

namespace App\Enums;

enum StockDifferenceReason: string
{
    case CountingError = 'counting_error';
    case Damage = 'damage';
    case Missing = 'missing';
    case Other = 'other';

    public function label(): string
    {
        return match ($this) {
            self::CountingError => 'Counting error',
            self::Damage => 'Damage',
            self::Missing => 'Missing',
            self::Other => 'Other',
        };
    }
}
