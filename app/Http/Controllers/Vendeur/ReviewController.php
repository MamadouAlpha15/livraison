<?php

namespace App\Http\Controllers\Vendeur;

use App\Http\Controllers\Controller;
use App\Models\Review;
use Illuminate\Support\Facades\Auth;

class ReviewController extends Controller
{
    public function index()
    {
        // Tous les avis liés aux produits de la boutique du vendeur connecté
        $reviews = Review::where('vendeur_id', Auth::id())->latest()->paginate(10); // Pagination

        return view('vendeur.reviews.index', compact('reviews'));
    }
}
