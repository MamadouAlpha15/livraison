<?php

namespace App\PageBuilder\Media;

use App\PageBuilder\Settings\Video;
use App\Services\ImageOptimizer;

/**
 * Valeur d'un réglage « vidéo » prête à afficher : MP4 envoyé par la boutique, YouTube (lecteur sans
 * cookies, chargé au clic) ou TikTok. La valeur a déjà été validée par Settings\Video.
 */
class VideoSource
{
    /** ['kind' => mp4|youtube|tiktok, 'src' => url, 'id' => ?, 'thumb' => ?] ou null */
    public static function from(?string $value): ?array
    {
        if (!$value) {
            return null;
        }
        if (str_starts_with($value, 'page-builder/')) {
            return ['kind' => 'mp4', 'src' => ImageOptimizer::url($value), 'id' => null, 'thumb' => null];
        }
        if (!preg_match(Video::LINK, $value)) {
            return null;
        }
        if (preg_match('~(?:v=|shorts/|youtu\.be/)([A-Za-z0-9_-]{6,20})~', $value, $m)) {
            return [
                'kind'  => 'youtube',
                'id'    => $m[1],
                'src'   => 'https://www.youtube-nocookie.com/embed/' . $m[1] . '?autoplay=1&rel=0&playsinline=1',
                'thumb' => 'https://i.ytimg.com/vi/' . $m[1] . '/hqdefault.jpg',
            ];
        }
        if (preg_match('~/video/(\d{6,25})~', $value, $m)) {
            return ['kind' => 'tiktok', 'id' => $m[1], 'src' => 'https://www.tiktok.com/embed/v2/' . $m[1], 'thumb' => null];
        }
        return null;
    }
}
