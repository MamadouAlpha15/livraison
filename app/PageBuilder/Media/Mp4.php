<?php

namespace App\PageBuilder\Media;

/**
 * Lecture minimale d'un fichier MP4, sans outil externe : vérifie l'en-tête « ftyp » et lit la durée
 * dans la boîte « mvhd » (versions 0 et 1). Les vidéos ne sont pas réencodées : on refuse simplement
 * celles qui sont trop longues.
 */
class Mp4
{
    /** Durée en secondes, ou null si le fichier n'est pas un MP4 lisible */
    public static function duration(string $path): ?float
    {
        $data = @file_get_contents($path);
        if ($data === false || strlen($data) < 32 || substr($data, 4, 4) !== 'ftyp') {
            return null;
        }
        $pos = strpos($data, 'mvhd');
        if ($pos === false) {
            return null;
        }
        $o = $pos + 4;
        $version = ord($data[$o] ?? "\x00");
        if ($version === 1) {
            $scale = unpack('N', substr($data, $o + 20, 4))[1] ?? 0;
            $duration = unpack('J', substr($data, $o + 24, 8))[1] ?? 0;
        } else {
            $scale = unpack('N', substr($data, $o + 12, 4))[1] ?? 0;
            $duration = unpack('N', substr($data, $o + 16, 4))[1] ?? 0;
        }
        return $scale > 0 ? $duration / $scale : null;
    }
}
