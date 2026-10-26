<?php

namespace App\Support;

use Illuminate\Support\Carbon;

/**
 * Créneau de livraison souhaité par le client. Le client choisit parmi les créneaux
 * encore possibles aujourd'hui et ceux de demain ; la commande garde le libellé lisible.
 */
class DeliverySlot
{
    /** Créneaux d'une journée : clé => [libellé, heure de fin]. */
    private const PERIODS = [
        'matin'     => ['matin (8h–12h)', 12],
        'apresmidi' => ['après-midi (12h–17h)', 17],
        'soir'      => ['soir (17h–20h)', 20],
    ];

    /** Options proposées maintenant : ['' => 'Dès que possible', '2026-10-22|matin' => 'Aujourd'hui · matin (8h–12h)', …]. */
    public static function options(?Carbon $now = null): array
    {
        $now = $now ?? now();
        $options = ['' => 'Dès que possible'];

        foreach ([0, 1] as $offset) {
            $day = $now->copy()->addDays($offset)->startOfDay();
            foreach (self::PERIODS as $key => [$label, $endHour]) {
                // Aujourd'hui : on garde un créneau s'il reste au moins 2 h avant sa fin
                if ($offset === 0 && $now->hour >= $endHour - 2) continue;
                $options[$day->toDateString() . '|' . $key] = self::dayName($day, $now) . ' · ' . $label;
            }
        }

        return $options;
    }

    /** Libellé à enregistrer sur la commande pour une clé choisie, null pour « dès que possible » ou une clé périmée. */
    public static function labelFor(?string $key): ?string
    {
        if (! $key) return null;
        [$date, $period] = array_pad(explode('|', $key, 2), 2, null);
        if (! isset(self::PERIODS[$period]) || ! array_key_exists($key, self::options())) return null;

        return ucfirst(Carbon::parse($date)->translatedFormat('D j M')) . ' · ' . self::PERIODS[$period][0];
    }

    private static function dayName(Carbon $day, Carbon $now): string
    {
        return $day->isSameDay($now) ? "Aujourd'hui" : 'Demain';
    }
}
