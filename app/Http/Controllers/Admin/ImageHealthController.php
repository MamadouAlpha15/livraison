<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BrokenImageReport;
use App\Notifications\BrokenProductImageNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Auth;

class ImageHealthController extends Controller
{
    public function index(Request $request)
    {
        $reports = BrokenImageReport::with(['product', 'shop.owner'])
            ->whereIn('status', ['open', 'notified'])
            ->latest()
            ->paginate(20);

        $stats = [
            'open'     => BrokenImageReport::where('status', 'open')->count(),
            'notified' => BrokenImageReport::where('status', 'notified')->count(),
            'resolved' => BrokenImageReport::where('status', 'resolved')->count(),
        ];

        return view('admin.image-health.index', compact('reports', 'stats'));
    }

    /** Relance la vérification tout de suite (au lieu d'attendre le passage automatique de 3h). */
    public function checkNow()
    {
        Artisan::call('products:check-images');
        $output = trim(Artisan::output());

        return redirect()->route('admin.image-health.index')->with('success', $output ?: 'Vérification terminée.');
    }

    /** Envoie l'alerte dans le compte Shopio du propriétaire de la boutique. */
    public function notify(BrokenImageReport $report)
    {
        $report->load(['product', 'shop.owner']);

        if (!$report->shop || !$report->shop->owner) {
            return back()->with('error', 'Impossible de trouver le propriétaire de cette boutique.');
        }
        if (!$report->product) {
            return back()->with('error', 'Ce produit a été supprimé depuis.');
        }

        $report->shop->owner->notify(new BrokenProductImageNotification($report->product));

        $report->update([
            'status'      => 'notified',
            'notified_at' => now(),
            'notified_by' => Auth::id(),
        ]);

        return back()->with('success', "Le propriétaire de « {$report->shop->name} » a été prévenu dans son compte Shopio.");
    }

    /** Marque un signalement comme résolu manuellement (photo vérifiée bonne, faux positif, etc.). */
    public function resolve(BrokenImageReport $report)
    {
        $report->update(['status' => 'resolved', 'resolved_at' => now()]);

        return back()->with('success', 'Signalement marqué comme résolu.');
    }
}
