<?php

namespace App\Enums;

enum StockTransactionType: string
{
    /** Stock present before the system went live. */
    case Opening = 'opening';

    /** Accepted quantity from a delivery. */
    case Receipt = 'receipt';

    /** Material issued to production. */
    case Usage = 'usage';

    /** Correction from a physical stock check. */
    case Adjustment = 'adjustment';

    /** Written off after entering stock. */
    case Damage = 'damage';

    /** Sent back to the supplier. */
    case Return_ = 'return';

    public function label(): string
    {
        return match ($this) {
            self::Opening => 'Opening stock',
            self::Receipt => 'Received',
            self::Usage => 'Used',
            self::Adjustment => 'Adjustment',
            self::Damage => 'Damaged',
            self::Return_ => 'Returned',
        };
    }

    /** Whether this type normally adds to stock. */
    public function isInward(): bool
    {
        return in_array($this, [self::Opening, self::Receipt], true);
    }
}
