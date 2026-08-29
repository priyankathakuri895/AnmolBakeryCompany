<?php

namespace App\Enums;

enum ReceiptType: string
{
    /** A normal, first delivery against a bill. */
    case Initial = 'initial';

    /** A later delivery that settles pending or damaged quantity of an earlier receipt. */
    case FollowUp = 'follow_up';

    public function label(): string
    {
        return match ($this) {
            self::Initial => 'Initial delivery',
            self::FollowUp => 'Follow-up delivery',
        };
    }
}
