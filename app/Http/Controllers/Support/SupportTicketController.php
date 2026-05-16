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
    public function index(Request $request)
    {
        $u = Auth::user();

        if ($u->role === 'superadmin') {
            $query = SupportTicket::with('shop', 'creator')->withCount('messages')->latest();

            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }
            if ($request->filled('shop_id')) {
                $query->where('shop_id', $request->shop_id);
            }
            if ($request->filled('search')) {
                $q = $request->search;
                $query->where(function ($sub) use ($q) {
                    $sub->where('subject', 'like', "%$q%")
                        ->orWhereHas('creator', fn($u) => $u->where('name', 'like', "%$q%"));
                });
            }

            $tickets = $query->paginate(25)->withQueryString();

            $stats = [
                'total'   => SupportTicket::count(),
                'open'    => SupportTicket::where('status', 'open')->count(),
                'closed'  => SupportTicket::where('status', 'closed')->count(),
                'pending' => SupportTicket::where('status', 'open')
                    ->whereDoesntHave('messages', fn($q) => $q->whereHas('author', fn($u) => $u->where('role', 'superadmin')))
                    ->count(),
            ];

            $shops = \App\Models\Shop::orderBy('name')->get(['id', 'name']);

            return view('admin.support.index', compact('tickets', 'stats', 'shops'));
        }

        $shop = null;

        if (in_array($u->role, ['admin', 'employe', 'vendeur']) || $u->role_in_shop) {
            $shop    = $u->shop;
            $tickets = SupportTicket::with('shop', 'creator')
                ->withCount('messages')
                ->where('shop_id', $u->shop_id)->latest()->paginate(20);
        } else {
            $tickets = SupportTicket::with('shop', 'creator')
                ->withCount('messages')
                ->where('user_id', $u->id)->latest()->paginate(20);
        }

        return view('support.index', compact('tickets', 'shop'));
    }

    public function create()
    {
        $u    = Auth::user();
        $shop = $u->shop;
        return view('support.create', compact('shop'));
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

        // Notifier le SuperAdmin (destinataire unique : la plateforme)
        foreach (User::where('role', 'superadmin')->get() as $to) {
            $to->notify(new NewSupportTicket($ticket));
        }

        return redirect()->route('support.show', $ticket)->with('success','Ticket créé.');
    }

    public function show(SupportTicket $ticket)
    {
        $this->authorizeTicket($ticket);
        $ticket->load(['creator', 'shop', 'messages.author']);
        $u = Auth::user();

        if ($u->role === 'superadmin') {
            return view('admin.support.show', compact('ticket'));
        }

        $shop = (in_array($u->role, ['admin', 'employe', 'vendeur']) || $u->role_in_shop) ? $u->shop : null;
        return view('support.show', compact('ticket', 'shop'));
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
