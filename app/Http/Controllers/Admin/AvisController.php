<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Review;
use App\Models\DeliveryCompanyReview;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AvisController extends Controller
{
    public function index(Request $request)
    {
        $meInit = strtoupper(substr(Auth::user()->name ?? 'S', 0, 1));
        $tab    = $request->input('tab', 'boutiques');
        $search = $request->input('search');
        $rating = $request->input('rating');

        $statsShop = [
            'total' => Review::count(),
            'avg'   => Review::count() ? round(Review::avg('rating'), 1) : 0,
            'bad'   => Review::where('rating', '<=', 2)->count(),
        ];
        $statsCo = [
            'total' => DeliveryCompanyReview::count(),
            'avg'   => DeliveryCompanyReview::count() ? round(DeliveryCompanyReview::avg('rating'), 1) : 0,
            'bad'   => DeliveryCompanyReview::where('rating', '<=', 2)->count(),
        ];

        if ($tab === 'entreprises') {
            $query = DeliveryCompanyReview::with(['company', 'user'])->latest();

            if ($rating) {
                $query->where('rating', $rating);
            }
            if ($search) {
                $query->where(function ($q) use ($search) {
                    $q->whereHas('company', fn($q2) => $q2->where('name', 'like', "%{$search}%"))
                      ->orWhereHas('user',    fn($q2) => $q2->where('name', 'like', "%{$search}%"));
                });
            }

            $reviews = $query->paginate(25)->withQueryString();
        } else {
            $query = Review::with(['order.shop', 'client', 'livreur'])->latest();

            if ($rating) {
                $query->where('rating', $rating);
            }
            if ($search) {
                $query->where(function ($q) use ($search) {
                    $q->whereHas('order.shop', fn($q2) => $q2->where('name', 'like', "%{$search}%"))
                      ->orWhereHas('client',    fn($q2) => $q2->where('name', 'like', "%{$search}%"));
                });
            }

            $reviews = $query->paginate(25)->withQueryString();
        }

        return view('admin.avis.index', compact(
            'reviews', 'tab', 'search', 'rating', 'statsShop', 'statsCo', 'meInit'
        ));
    }

    public function destroyShop(Review $review)
    {
        $review->delete();
        return back()->with('deleted', 'Avis boutique supprimé.');
    }

    public function destroyCompany(DeliveryCompanyReview $review)
    {
        $review->delete();
        return back()->with('deleted', 'Avis entreprise supprimé.');
    }
}
