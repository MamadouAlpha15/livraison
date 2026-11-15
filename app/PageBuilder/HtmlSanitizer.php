<?php

namespace App\PageBuilder;

use HTMLPurifier;
use HTMLPurifier_Config;

/**
 * Nettoyage du texte riche par liste blanche (HTMLPurifier) : paragraphes, gras, italique, souligné,
 * listes, intertitres, liens http(s) / mailto / tel. Aucun script, style, iframe ni attribut d'événement.
 */
class HtmlSanitizer
{
    private static ?HTMLPurifier $purifier = null;

    public static function clean(string $html): string
    {
        return trim(self::purifier()->purify($html));
    }

    private static function purifier(): HTMLPurifier
    {
        if (self::$purifier) {
            return self::$purifier;
        }
        $cache = storage_path('framework/htmlpurifier');
        if (!is_dir($cache)) {
            @mkdir($cache, 0775, true);
        }

        $config = HTMLPurifier_Config::createDefault();
        $config->set('HTML.Allowed', 'p,br,strong,b,em,i,u,ul,ol,li,h3,h4,blockquote,a[href|target|rel]');
        $config->set('Attr.AllowedFrameTargets', ['_blank']);
        $config->set('HTML.TargetNoopener', true);
        $config->set('HTML.TargetNoreferrer', true);
        $config->set('URI.AllowedSchemes', ['http' => true, 'https' => true, 'mailto' => true, 'tel' => true]);
        $config->set('AutoFormat.RemoveEmpty', true);
        $config->set('Cache.SerializerPath', is_dir($cache) && is_writable($cache) ? $cache : null);
        if (!is_dir($cache) || !is_writable($cache)) {
            $config->set('Cache.DefinitionImpl', null);
        }

        return self::$purifier = new HTMLPurifier($config);
    }
}
