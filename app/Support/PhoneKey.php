<?php

namespace App\Support;

/**
 * Clé d'un numéro guinéen : ses 9 derniers chiffres, quel que soit le format saisi
 * (« 622 45 81 37 », « +224622458137 », « 00224 622458137 »). Sert à reconnaître un même client
 * d'une commande à l'autre (orders.client_phone_key).
 */
class PhoneKey
{
    public static function of(?string $phone): ?string
    {
        $digits = preg_replace('/\D/', '', (string) $phone);
        if (str_starts_with($digits, '00224')) {
            $digits = substr($digits, 5);
        } elseif (strlen($digits) > 9 && str_starts_with($digits, '224')) {
            $digits = substr($digits, 3);
        }
        return strlen($digits) >= 8 ? substr($digits, -9) : null;
    }

    /** Numéro affichable sans tout dévoiler : +224 6•• •• 81 37 */
    public static function masked(?string $phone): string
    {
        $key = self::of($phone);
        return $key ? '+224 ' . $key[0] . '•• •• ' . substr($key, -4, 2) . ' ' . substr($key, -2) : '';
    }
}
