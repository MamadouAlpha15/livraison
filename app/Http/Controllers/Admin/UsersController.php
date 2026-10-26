<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class UsersController extends Controller
{
    /**
     * Comptes de l'équipe Shopio créés et retirés par le superadmin :
     * « staff » (tout le panneau admin, sans suppression définitive) et
     * « marketing » (uniquement les pages Campagnes).
     */
    public const TEAM_ROLES = ['staff', 'marketing'];

    public function index(Request $request)
    {
        $role   = $request->input('role', 'all');
        $search = $request->input('search');

        $query = User::with(['shop', 'deliveryCompany', 'ownedCompany'])->latest();

        if ($role !== 'all') {
            if ($role === 'employe') {
                $query->whereIn('role', ['employe', 'admin', 'vendeur'])
                      ->whereNotNull('shop_id');
            } elseif ($role === 'company') {
                $query->where(function ($q) {
                    $q->where('role', 'company')
                      ->orWhere(function ($q2) {
                          $q2->whereNotNull('company_id')->where('role', '!=', 'livreur');
                      });
                });
            } else {
                $query->where('role', $role);
            }
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name',  'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        $users = $query->paginate(30)->withQueryString();

        $stats = [
            'total'      => User::count(),
            'clients'    => User::where('role', 'client')->count(),
            'livreurs'   => User::where('role', 'livreur')->count(),
            'employes'   => User::whereIn('role', ['employe', 'vendeur', 'admin'])->whereNotNull('shop_id')->count(),
            'entreprises'=> User::where(function ($q) {
                $q->where('role', 'company')
                  ->orWhere(function ($q2) {
                      $q2->whereNotNull('company_id')->where('role', '!=', 'livreur');
                  });
            })->count(),
            'staff'      => User::whereIn('role', self::TEAM_ROLES)->count(),
        ];

        return view('admin.users.index', compact('users', 'stats', 'role', 'search'));
    }

    /**
     * Crée un membre du personnel plateforme (rôle "staff") : accès à tout le
     * panneau SuperAdmin en lecture/opérationnel, SANS accès à la zone de
     * suppression définitive (réservée au superadmin, fondateur). Réservé au
     * superadmin (voir middleware sur la route) — un staff ne peut pas créer
     * d'autre staff.
     */
    public function storeStaff(Request $request)
    {
        $data = $request->validate([
            'name'     => ['required', 'string', 'max:255'],
            'email'    => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8'],
            'role'     => ['nullable', 'in:' . implode(',', self::TEAM_ROLES)],
        ]);

        User::create([
            'name'              => $data['name'],
            'email'             => $data['email'],
            'password'          => bcrypt($data['password']),
            'role'              => $data['role'] ?? 'staff',
            'email_verified_at' => now(),
        ]);

        return back()->with('success', "Membre du personnel « {$data['name']} » créé avec succès. Il/elle peut se connecter avec l'email et le mot de passe fournis.");
    }

    /**
     * Retire un membre du personnel (supprime le compte staff — pas les comptes
     * client/vendeur/livreur/etc., uniquement role=staff, par sécurité).
     */
    public function destroyStaff(User $user)
    {
        if (!in_array($user->role, self::TEAM_ROLES, true)) {
            abort(403, 'Seuls les comptes du personnel plateforme peuvent être retirés ici.');
        }

        $name = $user->name;
        $user->delete();

        return back()->with('success', "Accès de « {$name} » retiré.");
    }
}
