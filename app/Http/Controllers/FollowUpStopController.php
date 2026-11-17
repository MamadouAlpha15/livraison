<?php

namespace App\Http\Controllers;

use App\Models\Shop;
use App\Models\User;
use App\Services\FollowUpService;
use Illuminate\Http\Request;

/** Lien « Ne plus recevoir ces rappels » des relances (signé, sans connexion). Définitif. */
class FollowUpStopController extends Controller
{
    public function __invoke(Request $request, User $user, FollowUpService $followUps)
    {
        $shop = $request->filled('shop') ? Shop::find($request->integer('shop')) : null;
        $followUps->optOut($user->id, $shop?->id, 'lien');
        return view('followups.stopped', ['shop' => $shop]);
    }
}
