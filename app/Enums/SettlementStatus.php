<?php

namespace App\Enums;

enum SettlementStatus: string
{
    /** Returns being counted; nothing posted to stock or debit yet. */
    case Draft = 'draft';

    /** Returns locked in and posted to stock; payment not yet recorded. */
    case PendingPayment = 'pending_payment';

    /** Posted: stock returns and the debit ledger have been updated. */
    case Finalized = 'finalized';

    public function label(): string
    {
        return match ($this) {
            self::Draft => 'Draft',
            self::PendingPayment => 'Awaiting payment',
            self::Finalized => 'Finalized',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Draft => 'amber',
            self::PendingPayment => 'amber',
            self::Finalized => 'green',
        };
    }
}
