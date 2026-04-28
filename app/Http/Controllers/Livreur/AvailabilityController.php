<?php

namespace App\Http\Controllers\Livreur;

use App\Http\Controllers\Controller;
use App\Models\Driver;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class AvailabilityController extends Controller
{
    public function toggle()
    {
        $user = Auth::user();

        if (!in_array($user->role, ['livreur']) && $user->role_in_shop !== 'livreur') {
            abort(403);
        }

        $goingOnline = ! $user->is_available;

        // Mettre a jour is_available sur le User
        $user->is_available = $goingOnline;
        $user->save();

        // Synchroniser le Driver lie (via user_id)
        $driver = Driver::where('user_id', $user->id)->first();
        if ($driver) {
            if ($goingOnline) {
                // En ligne -> disponible (sauf si deja en mission)
                if ($driver->status !== 'busy') {
                    $driver->update(['status' => 'available']);
                }
            } else {
                // Hors ligne -> offline
                $driver->update(['status' => 'offline']);
            }
        }

        $message = $goingOnline
            ? 'Vous etes maintenant EN LIGNE et disponible pour les livraisons.'
            : 'Vous etes maintenant HORS LIGNE.';

        return back()->with('success', $message);
    }
}
