<?php

namespace App\Enums;

/** Commission : en attente du délai de validation → validée (retirable) → payée | annulée (remboursement). */
enum CommissionStatus: string
{
    case Pending = 'pending';
    case Approved = 'approved';
    case Paid = 'paid';
    case Cancelled = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::Pending   => 'En attente',
            self::Approved  => 'Disponible',
            self::Paid      => 'Payée',
            self::Cancelled => 'Annulée',
        };
    }

    public function tone(): string
    {
        return match ($this) {
            self::Pending   => 'warn',
            self::Approved  => 'info',
            self::Paid      => 'ok',
            self::Cancelled => 'err',
        };
    }
}
