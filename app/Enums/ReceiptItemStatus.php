<?php

namespace App\Enums;

enum ReceiptItemStatus: string
{
    case Pending = 'pending';
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
}
