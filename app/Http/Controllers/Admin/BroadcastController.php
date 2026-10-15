<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Broadcast;
use App\Models\BroadcastRecipient;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class BroadcastController extends Controller
{
    private const TARGETS = [
        'client'  => 'Clients',
        'admin'   => 'Vendeurs (boutiques)',
        'company' => 'Entreprises de livraison',
    ];

    public function index(Request $request)
    {
        $broadcasts = Broadcast::with('sender')->latest()->paginate(15);

        $counts = [];
        foreach (self::TARGETS as $role => $label) {
            $counts[$role] = User::where('role', $role)
                ->whereNotNull('email')->where('email', '!=', '')
                ->count();
        }

        return view('admin.broadcasts.index', [
            'broadcasts' => $broadcasts,
            'counts'     => $counts,
            'targets'    => self::TARGETS,
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'target_role' => ['required', Rule::in(array_keys(self::TARGETS))],
            'subject'     => ['required', 'string', 'max:200'],
            'body'        => ['required', 'string', 'max:5000'],
        ]);

        $users = User::where('role', $data['target_role'])
            ->whereNotNull('email')->where('email', '!=', '')
            ->get(['id', 'name', 'email']);

        if ($users->isEmpty()) {
            return back()->withInput()->with('error', 'Aucun destinataire trouvé pour ce groupe (aucun email enregistré).');
        }

        $broadcast = Broadcast::create([
            'target_role'     => $data['target_role'],
            'subject'         => $data['subject'],
            'body'            => $data['body'],
            'recipient_count' => $users->count(),
            'status'          => 'queued',
            'sent_by'         => Auth::id(),
        ]);

        $now  = now();
        $rows = $users->map(fn ($u) => [
            'broadcast_id' => $broadcast->id,
            'user_id'      => $u->id,
            'email'        => $u->email,
            'name'         => $u->name,
            'status'       => 'pending',
            'created_at'   => $now,
            'updated_at'   => $now,
        ])->all();

        foreach (array_chunk($rows, 500) as $chunk) {
            BroadcastRecipient::insert($chunk);
        }

        return redirect()->route('admin.broadcasts.index')->with(
            'success',
            "Annonce créée pour {$users->count()} destinataire(s) (" . self::TARGETS[$data['target_role']] . "). "
            . "L'envoi se fait progressivement en arrière-plan — la page se met à jour automatiquement."
        );
    }

    /** Polling AJAX : progression des campagnes en cours. */
    public function progress()
    {
        $active = Broadcast::where('status', 'queued')
            ->get(['id', 'sent_count', 'failed_count', 'recipient_count', 'status']);

        return response()->json($active);
    }
}
