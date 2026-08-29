<?php

namespace App\Enums;

enum StockCheckStatus: string
{
    /** Being counted; no adjustments written yet. */
    case Draft = 'draft';

    /** Counted and posted; adjustment transactions exist. */
    case Finalized = 'finalized';

    case Cancelled = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::Draft => 'Draft',
            self::Finalized => 'Finalized',
            self::Cancelled => 'Cancelled',
        };
    }
}
