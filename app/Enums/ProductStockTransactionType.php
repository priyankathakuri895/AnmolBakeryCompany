<?php

namespace App\Enums;

enum ProductStockTransactionType: string
{
    /** Stock present before the system went live. */
    case Opening = 'opening';

    /** Manually recorded factory output. */
    case Production = 'production';

    /** Correction from a physical stock check. */
    case Adjustment = 'adjustment';

    /** Loaded onto a van for the day (or a load being reversed). */
    case VanLoad = 'van_load';

    /** Unsold-but-fresh product a van brought back. */
    case VanReturn = 'van_return';

    public function label(): string
    {
        return match ($this) {
            self::Opening => 'Opening stock',
            self::Production => 'Production',
            self::Adjustment => 'Adjustment',
            self::VanLoad => 'Van load',
            self::VanReturn => 'Van return',
        };
    }
}
