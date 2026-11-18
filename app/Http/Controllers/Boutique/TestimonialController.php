<?php

namespace App\Http\Controllers\Boutique;

use App\Http\Controllers\Controller;
use App\Models\Shop;
use App\Models\ShopTestimonial;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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
        ], [
            'rating.required' => 'Choisis une note.',
            'text.required'   => 'Écris quelques mots sur ton expérience.',
            'text.min'        => 'Écris au moins 20 caractères.',
            'consent.accepted'=> 'Coche la case pour accepter l’affichage sur la page d’accueil.',
        ]);

        ShopTestimonial::updateOrCreate(['shop_id' => $shop->id], [
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
        ShopTestimonial::where('shop_id', $this->shop()->id)->delete();
        return redirect()->route('boutique.testimonial.edit')->with('success', 'Ton avis a été retiré.');
    }
}
