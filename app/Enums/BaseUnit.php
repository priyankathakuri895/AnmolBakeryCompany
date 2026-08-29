<?php

namespace App\Enums;

enum BaseUnit: string
{
    case Kilogram = 'kg';
    case Gram = 'g';
    case Litre = 'l';
    case Millilitre = 'ml';
    case Piece = 'pcs';

    public function label(): string
    {
        return match ($this) {
            self::Kilogram => 'kg',
            self::Gram => 'g',
            self::Litre => 'L',
            self::Millilitre => 'ml',
            self::Piece => 'pcs',
        };
    }
}
