<?php

namespace App\Http\Controllers\Boutique;

use App\Http\Controllers\Controller;
use App\Models\Shop;
use App\Models\ShopTestimonial;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

/**
 * « Ton avis sur Shopio » : le propriétaire de la boutique écrit son témoignage.
 * Il apparaît sur la page d'accueil seulement après validation par l'équipe Shopio,
 * et repasse « en attente » à chaque modification.
 */
class TestimonialController extends Controller
{
    private function shop(): Shop
    {
        $user = Auth::user();
        $shop = $user->shop;
        abort_unless($shop && (int) $shop->user_id === (int) $user->id, 403, 'Seul le propriétaire de la boutique peut donner son avis.');
        return $shop;
    }

    public function edit(): View
    {
        $shop = $this->shop();
        $delivered = $shop->orders()->where('status', 'livrée')->count();
        return view('boutique.testimonial', [
            'shop'        => $shop,
            'testimonial' => ShopTestimonial::where('shop_id', $shop->id)->first(),
            'delivered'   => $delivered,
            'firstName'   => trim(explode(' ', trim((string) Auth::user()->name))[0] ?? ''),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $shop = $this->shop();
        $data = $request->validate([
            'rating'      => ['required', 'integer', 'between:1,5'],
            'text'        => ['required', 'string', 'min:20', 'max:400'],
            'author'      => ['required', 'string', 'min:2', 'max:60'],
            'author_role' => ['nullable', 'string', 'max:60'],
            'consent'     => ['accepted'],
            // Vidéo courte facultative (1 min 30 au plus, vérifiée aussi dans le navigateur)
            'video'       => ['nullable', 'file', 'mimetypes:video/mp4,video/webm,video/quicktime', 'max:51200'],
            'remove_video'=> ['nullable', 'boolean'],
        ], [
            'video.mimetypes' => 'La vidéo doit être au format MP4, WebM ou MOV.',
            'video.max'       => 'La vidéo est trop lourde : 50 Mo au maximum.',
            'video.uploaded'  => 'La vidéo n’a pas pu être envoyée. Elle est peut-être trop lourde.',
            'rating.required' => 'Choisis une note.',
            'text.required'   => 'Écris quelques mots sur ton expérience.',
            'text.min'        => 'Écris au moins 20 caractères.',
            'consent.accepted'=> 'Coche la case pour accepter l’affichage sur la page d’accueil.',
        ]);

        $current = ShopTestimonial::where('shop_id', $shop->id)->first();
        $video = $current?->video;
        if (($request->boolean('remove_video') || $request->hasFile('video')) && $video) {
            Storage::disk('public')->delete($video);
            $video = null;
        }
        if ($request->hasFile('video')) {
            $video = $request->file('video')->store('testimonials/videos', 'public');
        }

        ShopTestimonial::updateOrCreate(['shop_id' => $shop->id], [
            'video'       => $video,
            'user_id'     => Auth::id(),
            'rating'      => $data['rating'],
            'text'        => trim(strip_tags($data['text'])),
            'author'      => trim(strip_tags($data['author'])),
            'author_role' => trim(strip_tags((string) ($data['author_role'] ?? ''))) ?: null,
            'status'      => 'pending',
            'approved_at' => null,
        ]);

        return redirect()->route('boutique.testimonial.edit')
            ->with('success', 'Merci ! Ton avis sera visible sur la page d’accueil après validation par l’équipe Shopio.');
    }

    public function destroy(): RedirectResponse
    {
        $t = ShopTestimonial::where('shop_id', $this->shop()->id)->first();
        if ($t?->video) Storage::disk('public')->delete($t->video);
        $t?->delete();
        return redirect()->route('boutique.testimonial.edit')->with('success', 'Ton avis a été retiré.');
    }
}
