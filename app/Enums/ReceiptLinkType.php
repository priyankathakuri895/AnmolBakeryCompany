<?php

namespace App\Enums;

enum ReceiptLinkType: string
{
    /** A line on a first delivery. */
    case Initial = 'initial';

    /** Settles quantity that was short on an earlier line. */
    case PendingFulfilment = 'pending_fulfilment';

    /** Replaces quantity that arrived damaged on an earlier line. */
    case DamageReplacement = 'damage_replacement';

    public function label(): string
    {
        return match ($this) {
            self::Initial => 'Initial',
            self::PendingFulfilment => 'Pending fulfilment',
            self::DamageReplacement => 'Damage replacement',
        };
    }
}
