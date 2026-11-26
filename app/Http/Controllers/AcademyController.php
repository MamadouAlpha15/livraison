<?php

namespace App\Http\Controllers;

use App\Support\Academy;
use Illuminate\View\View;

/** Académie Shopio (/academie) : guides pratiques pour vendre en ligne, et le groupe WhatsApp des vendeurs. */
class AcademyController extends Controller
{
    public function index(): View
    {
        return view('academy.index', ['guides' => Academy::GUIDES, 'community' => self::community()]);
    }

    public function show(string $slug): View
    {
        $guide = Academy::find($slug);
        abort_unless($guide, 404);
        $others = collect(Academy::GUIDES)->except($slug)->take(3);
        return view('academy.show', ['slug' => $slug, 'guide' => $guide, 'others' => $others, 'community' => self::community()]);
    }

    /** Lien d'invitation du groupe WhatsApp des vendeurs (config/shopio.php), ou null. */
    public static function community(): ?string
    {
        $url = trim((string) config('shopio.community_whatsapp'));
        return preg_match('#^https://(chat\.whatsapp\.com|whatsapp\.com/channel)/[A-Za-z0-9]+#', $url) ? $url : null;
    }
}
