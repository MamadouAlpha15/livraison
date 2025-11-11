<?php

// app/Http/Controllers/Support/SupportMessageController.php
namespace App\Http\Controllers\Support;

use App\Http\Controllers\Controller;
use App\Models\SupportTicket;
use App\Models\SupportMessage;
use App\Notifications\NewSupportMessage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SupportMessageController extends Controller
{
    public function store(Request $r, SupportTicket $ticket)
    {
        $this->authorizeTicket($ticket);

        $data = $r->validate(['body' => ['required','string','max:10000']]);

        $msg = SupportMessage::create([
            'ticket_id' => $ticket->id,
            'user_id'   => $r->user()->id,
            'body'      => $data['body'],
        ]);

        // Email la contre-partie : si client parle -> staff; si staff parle -> client
        $notifyUsers = collect();
        if ($r->user()->id === $ticket->user_id) {
            // créateur (client) a parlé -> notifier staff
            $notifyUsers = $this->staffRecipients($ticket);
        } else {
            // staff a parlé -> notifier le client
            $notifyUsers = collect([$ticket->creator]);
        }
        foreach ($notifyUsers as $u) {
            $u->notify(new NewSupportMessage($msg));
        }

        return back()->with('success','Message envoyé.');
    }

    // Endpoint JSON pour rafraichir la discussion (polling)
    public function listJson(SupportTicket $ticket)
    {
        $this->authorizeTicket($ticket);
        $messages = $ticket->messages()->with('author:id,name,role')->get();
        return response()->json($messages);
    }

    private function authorizeTicket(SupportTicket $t): void
    {
        $u = Auth::user();
        $ok = false;
        if ($u->id === $t->user_id) $ok = true;
        if ($u->role === 'superadmin') $ok = true;
        if ($u->shop_id && $t->shop_id && $u->shop_id === $t->shop_id) $ok = true;
        abort_unless($ok, 403, 'Non autorisé');
    }

    private function staffRecipients(SupportTicket $t)
    {
        return \App\Models\User::where('shop_id', $t->shop_id)
            ->where(function($q){
                $q->whereIn('role', ['admin','employe','vendeur'])
                  ->orWhereIn('role_in_shop', ['admin','employe','vendeur']);
            })->get();
    }
}
