<?php

// app/Http/Controllers/Livreur/AvailabilityController.php
namespace App\Http\Controllers\Livreur;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class AvailabilityController extends Controller
{
    public function toggle()
    {
        $user = Auth::user();

        // Vérifie que c’est bien un livreur
        if (!in_array($user->role, ['livreur']) && $user->role_in_shop !== 'livreur') {
            abort(403, 'Accès réservé aux livreurs');
        }

        // Inverse le statut
        $user->is_available = !$user->is_available;
        $user->save();

        $message = $user->is_available
            ? '🟢 Vous êtes maintenant EN LIGNE et disponible pour les livraisons.'
            : '🔴 Vous êtes maintenant HORS LIGNE.';

        return back()->with('success', $message);
    }
}
