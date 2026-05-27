<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use App\Models\DeliveryCompany;
use App\Models\SupportTicket;
use App\Models\SupportMessage;
use App\Models\User;
use App\Notifications\NewSupportTicket;
use App\Notifications\NewSupportMessage;
use App\Services\SubscriptionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class SupportController extends Controller
{
    private function company(): DeliveryCompany
    {
        $company = DeliveryCompany::forUser(Auth::user());
        abort_unless($company, 403);
        return $company;
    }

    public function index()
    {
        $user    = Auth::user();
        $company = $this->company();

        $ticket = SupportTicket::where('user_id', $user->id)->latest()->first();

        if (! $ticket) {
            $ticket = SupportTicket::create([
                'user_id' => $user->id,
                'subject' => 'Support · ' . $company->name,
                'status'  => 'open',
            ]);
            foreach (User::where('role', 'superadmin')->get() as $admin) {
                $admin->notify(new NewSupportTicket($ticket));
            }
        }

        $messages = $ticket->messages()->with('author:id,name,role')->oldest()->get();

        $svc          = app(SubscriptionService::class);
        $isBusiness   = $svc->companyPlan($company) === 'business';
        $maxDrivers   = SubscriptionService::COMP_FREE_MAX_DRIVERS;
        $maxZones     = SubscriptionService::COMP_FREE_MAX_ZONES;
        $maxOrders    = SubscriptionService::COMP_FREE_MAX_ORDERS;
        $totalDrivers = $company->drivers()->count();
        $totalZones   = $company->zones()->count();
        $usedOrders   = $svc->monthlyCompanyOrderCount($company);

        return view('company.support.index', compact(
            'ticket', 'company', 'messages',
            'isBusiness', 'maxDrivers', 'maxZones', 'maxOrders',
            'totalDrivers', 'totalZones', 'usedOrders'
        ));
    }

    public function send(Request $request, SupportTicket $ticket)
    {
        $user = Auth::user();
        abort_unless($user->id === $ticket->user_id, 403);
        abort_if($ticket->status === 'closed', 422, 'Ticket fermé.');

        $data = $request->validate(['body' => ['required', 'string', 'max:10000']]);

        $msg = SupportMessage::create([
            'ticket_id' => $ticket->id,
            'user_id'   => $user->id,
            'body'      => $data['body'],
        ]);

        foreach (User::where('role', 'superadmin')->get() as $admin) {
            $admin->notify(new NewSupportMessage($msg));
        }

        return response()->json(['ok' => true, 'msg' => $msg->load('author:id,name,role')]);
    }

    public function poll(SupportTicket $ticket)
    {
        $user = Auth::user();
        abort_unless($user->id === $ticket->user_id || $user->role === 'superadmin', 403);

        return response()->json(
            $ticket->messages()->with('author:id,name,role')->oldest()->get()
        );
    }

    public function hasUnread()
    {
        $user = Auth::user();
        $this->company();

        $ticket = SupportTicket::where('user_id', $user->id)->latest()->first();

        if (! $ticket) {
            return response()->json(['has_unread' => false]);
        }

        $adminIds = User::where('role', 'superadmin')->pluck('id');

        $lastAdminMsg = $ticket->messages()
            ->whereIn('user_id', $adminIds)
            ->latest()
            ->first();

        if (! $lastAdminMsg) {
            return response()->json(['has_unread' => false]);
        }

        return response()->json([
            'has_unread' => true,
            'msg_id'     => $lastAdminMsg->id,
            'last_body'  => Str::limit($lastAdminMsg->body, 80),
            'last_at'    => $lastAdminMsg->created_at->diffForHumans(),
        ]);
    }
}
