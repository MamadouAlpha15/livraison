<?php

namespace App\Support;

use Illuminate\Http\Request;
use MaxMind\Db\Reader;

/**
 * Pays d'un visiteur (code ISO à 2 lettres), pour la carte de la vue en direct.
 * L'adresse IP n'est utilisée qu'au moment de la lecture, jamais enregistrée.
 *  1. En-tête CF-IPCountry quand le site passe par Cloudflare ;
 *  2. sinon la base libre DB-IP Lite (CC BY 4.0) : storage/app/geoip/dbip-country-lite.mmdb,
 *     téléchargée et mise à jour par la commande « php artisan shopio:geoip-update ».
 * Adresse locale ou privée, ou base absente : null (le visiteur est compté, mais pas placé sur la carte).
 */
class GeoCountry
{
    private static ?Reader $reader = null;
    private static bool $tried = false;

    public static function path(): string
    {
        return storage_path('app/geoip/dbip-country-lite.mmdb');
    }

    public static function of(Request $request): ?string
    {
        $cf = strtoupper((string) $request->header('CF-IPCountry'));
        if (preg_match('/^[A-Z]{2}$/', $cf) && $cf !== 'XX' && $cf !== 'T1') return $cf;

        return self::lookup((string) $request->ip());
    }

    public static function lookup(string $ip): ?string
    {
        if (!filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) return null;
        $reader = self::reader();
        if (!$reader) return null;
        try {
            $code = strtoupper((string) ($reader->get($ip)['country']['iso_code'] ?? ''));
            return preg_match('/^[A-Z]{2}$/', $code) ? $code : null;
        } catch (\Throwable $e) {
            return null;
        }
    }

    private static function reader(): ?Reader
    {
        if (!self::$tried) {
            self::$tried = true;
            try { self::$reader = is_file(self::path()) ? new Reader(self::path()) : null; } catch (\Throwable $e) { self::$reader = null; }
        }
        return self::$reader;
    }
}
