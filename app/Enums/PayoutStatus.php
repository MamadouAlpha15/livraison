<?php

namespace App\Enums;

enum PayoutStatus: string
{
    case Requested = 'requested';
    case Paid = 'paid';
    case Rejected = 'rejected';

    public function label(): string
    {
        return match ($this) {
            self::Requested => 'Demandé',
            self::Paid      => 'Payé',
            self::Rejected  => 'Refusé',
        };
    }

    public function tone(): string
    {
        return match ($this) {
            self::Requested => 'warn',
            self::Paid      => 'ok',
            self::Rejected  => 'err',
        };
    }
}
