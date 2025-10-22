<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    // Marquer une notification comme lue
    public function read($id)
    {
        $notification = Auth::user()->unreadNotifications()->findOrFail($id);
        $notification->markAsRead();

        // Après avoir cliqué → redirection par défaut (par exemple commandes client)
        return redirect()->to($notification->data['url'] ?? url('/'));
    }

    // Tout marquer comme lu
    public function readAll()
    {
        Auth::user()->unreadNotifications->markAsRead();
        return back()->with('success', 'Toutes les notifications ont été marquées comme lues.');
    }
}
