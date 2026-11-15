<?php

namespace App\PageBuilder\Settings;

use App\PageBuilder\ValidationContext;

/** Vidéo : courte vidéo MP4 envoyée par la boutique, ou lien YouTube / TikTok */
class Video extends Setting
{
    public const LINK = '~^https://(www\.)?(youtube\.com/(watch\?v=|shorts/)[A-Za-z0-9_-]{6,20}|youtu\.be/[A-Za-z0-9_-]{6,20}|(www\.)?tiktok\.com/@[A-Za-z0-9_.-]{1,40}/video/\d{6,25})([?&#].*)?$~';

    public function type(): string { return 'video'; }

    public function rules(ValidationContext $ctx): array
    {
        $prefix = preg_quote($ctx->mediaPrefix(), '#');
        return ['nullable', 'string', 'max:255', function ($attribute, $value, $fail) use ($prefix) {
            if (!preg_match('#^' . $prefix . 'videos/[A-Za-z0-9]{20,40}\.mp4$#', $value) && !preg_match(self::LINK, $value)) {
                $fail('Vidéo non acceptée : envoie un MP4 ou colle un lien YouTube ou TikTok.');
            }
        }];
    }

    public function normalize(mixed $value, ValidationContext $ctx): mixed
    {
        return $value === null || $value === '' ? $this->default : trim($value);
    }
}
