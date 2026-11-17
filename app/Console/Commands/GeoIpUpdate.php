<?php

namespace App\Console\Commands;

use App\Support\GeoCountry;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

/**
 * Télécharge la base libre « DB-IP Lite Country » (CC BY 4.0, https://db-ip.com) utilisée par la vue en direct
 * pour connaître le pays d'un visiteur sans jamais enregistrer son adresse IP (App\Support\GeoCountry).
 * Une nouvelle version sort chaque mois : la commande est planifiée le 3 de chaque mois (routes/console.php).
 */
class GeoIpUpdate extends Command
{
    protected $signature = 'shopio:geoip-update';
    protected $description = 'Télécharge la base des pays DB-IP Lite (vue en direct des boutiques)';

    public function handle(): int
    {
        $dest = GeoCountry::path();
        @mkdir(dirname($dest), 0775, true);

        foreach ([now(), now()->subMonthNoOverflow()] as $month) {
            $url = 'https://download.db-ip.com/free/dbip-country-lite-' . $month->format('Y-m') . '.mmdb.gz';
            $gz = $dest . '.download.gz';
            try {
                $res = Http::timeout(120)->sink($gz)->get($url);
            } catch (\Throwable $e) {
                $this->warn('Téléchargement impossible : ' . $e->getMessage());
                continue;
            }
            if (!$res->successful()) { @unlink($gz); continue; }

            $tmp = $dest . '.new';
            $in = gzopen($gz, 'rb'); $out = fopen($tmp, 'wb');
            while (!gzeof($in)) fwrite($out, gzread($in, 1 << 20));
            gzclose($in); fclose($out); @unlink($gz);

            // Contrôle : la base doit répondre avant de remplacer l'ancienne
            try {
                (new \MaxMind\Db\Reader($tmp))->get('8.8.8.8');
            } catch (\Throwable $e) {
                @unlink($tmp);
                $this->error('Fichier reçu illisible : la base actuelle est gardée.');
                return self::FAILURE;
            }
            rename($tmp, $dest);
            $this->info('Base des pays à jour (' . $month->format('m/Y') . ') : ' . round(filesize($dest) / 1048576, 1) . ' Mo.');
            return self::SUCCESS;
        }

        $this->error('Aucune base disponible pour le moment. La vue en direct compte les visiteurs sans les placer sur la carte.');
        return self::FAILURE;
    }
}
