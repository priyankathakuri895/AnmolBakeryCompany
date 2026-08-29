<?php

namespace App\Enums;

enum ReceiptStatus: string
{
    /** Something on this receipt is still owed by the supplier. */
    case Pending = 'pending';

    /** Every line is settled. */
    case Complete = 'complete';

    case Cancelled = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Pending',
            self::Complete => 'Complete',
            self::Cancelled => 'Cancelled',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Pending => 'amber',
            self::Complete => 'green',
            self::Cancelled => 'gray',
        };
    }
}
