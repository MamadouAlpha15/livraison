<?php

// app/Http/Controllers/Support/SupportTicketController.php
namespace App\Http\Controllers\Support;

use App\Http\Controllers\Controller;
use App\Models\SupportTicket;
use App\Models\SupportMessage;
use App\Models\Shop;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Notifications\NewSupportTicket;

class SupportTicketController extends Controller
{
    public function index()
    {
        $u = Auth::user();

        // Clients : leurs tickets ; Staff/Admin : tickets de leur boutique ; Superadmin : tout
        if (in_array($u->role, ['superadmin'])) {
            $tickets = SupportTicket::with('shop','creator')->latest()->paginate(12);
        } elseif (in_array($u->role, ['admin','employe','vendeur']) || $u->role_in_shop) {
            $tickets = SupportTicket::with('shop','creator')
                ->where('shop_id', $u->shop_id)->latest()->paginate(12);
        } else { // client / livreur
            $tickets = SupportTicket::with('shop','creator')
                ->where('user_id', $u->id)->latest()->paginate(12);
        }

        return view('support.index', compact('tickets'));
    }

    public function create()
    {
        $u = Auth::user();
        // Clients choisissent la boutique ciblée (ou toutes) ; admins ont leur shop pré-sélectionné
        $shops = in_array($u->role, ['admin','employe','vendeur']) && $u->shop_id
            ? Shop::where('id', $u->shop_id)->get()
            : Shop::orderBy('name')->get();

        return view('support.create', compact('shops'));
    }

    public function store(Request $r)
    {
        $u = $r->user();
        $data = $r->validate([
            'shop_id' => ['nullable','exists:shops,id'],
            'subject' => ['required','string','max:160'],
            'message' => ['required','string','max:10000'],
        ]);

        // Staff : si pas de shop_id fourni, prendre celui du staff
        if (!$data['shop_id'] && $u->shop_id) $data['shop_id'] = $u->shop_id;

        $ticket = SupportTicket::create([
            'shop_id' => $data['shop_id'],
            'user_id' => $u->id,
            'subject' => $data['subject'],
            'status'  => 'open',
        ]);

        $first = SupportMessage::create([
            'ticket_id' => $ticket->id,
            'user_id'   => $u->id,
            'body'      => $data['message'],
        ]);

        // Notifier les destinataires (staff de la boutique ou superadmin)
        $recipients = collect();
        if ($ticket->shop_id) {
            $recipients = User::where('shop_id', $ticket->shop_id)
                ->where(function($q){
                    $q->whereIn('role', ['admin','employe','vendeur'])
                      ->orWhereIn('role_in_shop', ['admin','employe','vendeur']);
                })->get();
        } else {
            $recipients = User::where('role','superadmin')->get();
        }

        foreach ($recipients as $to) {
            $to->notify(new NewSupportTicket($ticket));
        }

        return redirect()->route('support.show', $ticket)->with('success','Ticket créé.');
    }

    public function show(SupportTicket $ticket)
    {
        $this->authorizeTicket($ticket);
        $ticket->load(['creator','shop','messages.author']);
        return view('support.show', compact('ticket'));
    }

    public function close(SupportTicket $ticket)
    {
        $this->authorizeTicket($ticket);
        $ticket->update(['status' => 'closed']);
        return redirect()->route('support.show', $ticket)->with('success','Ticket fermé.');
    }

    private function authorizeTicket(SupportTicket $t): void
    {
        $u = auth()->user();
        $ok = false;
        if ($u->id === $t->user_id) $ok = true;
        if ($u->role === 'superadmin') $ok = true;
        if ($u->shop_id && $t->shop_id && $u->shop_id === $t->shop_id) $ok = true;
        abort_unless($ok, 403, 'Non autorisé');
    }
}
