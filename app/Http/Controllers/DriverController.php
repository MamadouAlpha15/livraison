<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Driver;
use App\Models\DeliveryCompany;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class DriverController extends Controller
{
    /* ── Helper: récupère et valide l'entreprise de l'utilisateur connecté ── */
    private function resolveCompany(): DeliveryCompany
    {
        $company = DeliveryCompany::forUser(auth()->user());

        if (! $company) {
            abort(redirect()->route('company.dashboard')
                ->with('error', "Aucune entreprise liée à ce compte."));
        }

        return $company;
    }

    /* ── Sauvegarde photo base64 (envoyée depuis le Canvas client) ── */
    private function saveBase64Photo(string $base64, ?string $oldPath = null): string
    {
        if ($oldPath) {
            Storage::disk('public')->delete($oldPath);
        }

        // Extraire les données brutes (format: data:image/jpeg;base64,...)
        $imageData = preg_replace('/^data:image\/\w+;base64,/', '', $base64);
        $binary    = base64_decode($imageData);

        $path     = 'drivers/' . uniqid('drv_', true) . '.jpg';
        $fullPath = Storage::disk('public')->path($path);

        if (! is_dir(dirname($fullPath))) {
            mkdir(dirname($fullPath), 0755, true);
        }

        file_put_contents($fullPath, $binary);

        return $path;
    }

    /* ── Optimisation image via GD (fallback si pas de canvas) ── */
    private function saveOptimizedPhoto($file, ?string $oldPath = null): string
    {
        if ($oldPath) {
            Storage::disk('public')->delete($oldPath);
        }

        $maxDim  = 800;
        $quality = 82;

        $realPath = $file->getRealPath();
        [$origW, $origH] = getimagesize($realPath);

        $ratio = min($maxDim / $origW, $maxDim / $origH, 1.0);
        $newW  = (int) round($origW * $ratio);
        $newH  = (int) round($origH * $ratio);

        $mime = $file->getMimeType();
        $src  = match (true) {
            str_contains($mime, 'png')  => imagecreatefrompng($realPath),
            str_contains($mime, 'webp') => imagecreatefromwebp($realPath),
            str_contains($mime, 'gif')  => imagecreatefromgif($realPath),
            default                     => imagecreatefromjpeg($realPath),
        };

        $dst   = imagecreatetruecolor($newW, $newH);
        $white = imagecolorallocate($dst, 255, 255, 255);
        imagefill($dst, 0, 0, $white);
        imagecopyresampled($dst, $src, 0, 0, 0, 0, $newW, $newH, $origW, $origH);

        $path     = 'drivers/' . uniqid('drv_', true) . '.jpg';
        $fullPath = Storage::disk('public')->path($path);

        if (! is_dir(dirname($fullPath))) {
            mkdir(dirname($fullPath), 0755, true);
        }

        imagejpeg($dst, $fullPath, $quality);
        imagedestroy($src);
        imagedestroy($dst);

        return $path;
    }

    /* ────────────────────────────────────────────
     |  INDEX
     ──────────────────────────────────────────── */
    public function index()
    {
        $company = $this->resolveCompany();

        /* Stats sur tous les chauffeurs (avant pagination) */
        $allDrivers = $company->drivers()
            ->withCount([
                'orders as livrees_count' => fn($q) => $q->where('status', 'livrée'),
            ])
            ->get();

        $stats = [
            'total'     => $allDrivers->count(),
            'available' => $allDrivers->where('status', 'available')->count(),
            'busy'      => $allDrivers->where('status', 'busy')->count(),
            'offline'   => $allDrivers->where('status', 'offline')->count(),
            'livrees'   => $allDrivers->sum('livrees_count'),
        ];

        /* Liste paginée : plus récent en premier */
        $drivers = $company->drivers()
            ->withCount([
                'orders',
                'orders as livrees_count'  => fn($q) => $q->where('status', 'livrée'),
                'orders as en_cours_count' => fn($q) => $q->where('status', 'en_livraison'),
            ])
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return view('company.drivers.index', compact('company', 'drivers', 'stats'));
    }

    /* ────────────────────────────────────────────
     |  CREATE
     ──────────────────────────────────────────── */
    public function create()
    {
        $company = $this->resolveCompany();
        return view('company.drivers.create', compact('company'));
    }

    /* ────────────────────────────────────────────
     |  STORE
     ──────────────────────────────────────────── */
    public function store(Request $request)
    {
        $company = $this->resolveCompany();

        if (! $company->approved) {
            return redirect()->route('company.drivers.index')
                ->with('error', "Votre entreprise est en attente d'approbation.");
        }

        $data = $request->validate([
            'name'       => 'required|string|max:190',
            'phone'      => 'nullable|string|max:30',
            'email'      => 'nullable|email|max:190|unique:users,email',
            'password'   => 'nullable|string|min:6|max:100',
            'photo'      => 'nullable|image|mimes:jpeg,png,webp,gif|max:5120',
            'photo_data' => 'nullable|string',
        ]);

        $path = null;
        if (! empty($data['photo_data'])) {
            $path = $this->saveBase64Photo($data['photo_data']);
        } elseif ($request->hasFile('photo')) {
            $path = $this->saveOptimizedPhoto($request->file('photo'));
        }

        $rawPassword = $data['password'] ?? Str::random(10);

        // Créer le compte User pour la connexion
        $user = null;
        if (! empty($data['email'])) {
            $user = \App\Models\User::create([
                'name'                 => $data['name'],
                'email'                => $data['email'],
                'password'             => Hash::make($rawPassword),
                'role'                 => 'livreur',
                'phone'                => $data['phone'] ?? null,
                'must_change_password' => true,
            ]);
        }

        $company->drivers()->create([
            'user_id'              => $user?->id,
            'name'                 => $data['name'],
            'phone'                => $data['phone'] ?? null,
            'email'                => $data['email'] ?? null,
            'password'             => Hash::make($rawPassword),
            'must_change_password' => true,
            'photo'                => $path,
            'status'               => 'offline', // géré par le livreur depuis son dashboard
        ]);

        return redirect()->route('company.drivers.index')
            ->with('success', "Chauffeur « {$data['name']} » ajouté avec succès.");
    }

    /* ────────────────────────────────────────────
     |  EDIT
     ──────────────────────────────────────────── */
    public function edit(Driver $driver)
    {
        $company = $this->resolveCompany();
        abort_unless($driver->delivery_company_id === $company->id, 403);

        $driver->load('user');
        $driver->loadCount([
            'orders',
            'orders as livrees_count'  => fn($q) => $q->where('status', 'livrée'),
            'orders as en_cours_count' => fn($q) => $q->where('status', 'en_livraison'),
        ]);

        return view('company.drivers.edit', compact('company', 'driver'));
    }

    /* ────────────────────────────────────────────
     |  UPDATE
     ──────────────────────────────────────────── */
    public function update(Request $request, Driver $driver)
    {
        $company = $this->resolveCompany();
        abort_unless($driver->delivery_company_id === $company->id, 403);

        if (! $company->approved) {
            return redirect()->route('company.drivers.index')
                ->with('error', "Votre entreprise est en attente d'approbation.");
        }

        $emailRule = 'nullable|email|max:190|unique:users,email';
        if ($driver->user_id) {
            $emailRule .= ',' . $driver->user_id;
        }

        $data = $request->validate([
            'name'         => 'required|string|max:190',
            'phone'        => 'nullable|string|max:30',
            'email'        => $emailRule,
            'password'     => 'nullable|string|min:6|max:100',
            'photo'        => 'nullable|image|mimes:jpeg,png,webp,gif|max:5120',
            'photo_data'   => 'nullable|string',
            'remove_photo' => 'nullable|boolean',
        ]);

        // Suppression manuelle de la photo
        if ($request->boolean('remove_photo') && $driver->photo) {
            Storage::disk('public')->delete($driver->photo);
            $driver->photo = null;
        }

        // Nouvelle photo (canvas base64 prioritaire, sinon upload brut)
        if (! empty($data['photo_data'])) {
            $driver->photo = $this->saveBase64Photo($data['photo_data'], $driver->photo);
        } elseif ($request->hasFile('photo')) {
            $driver->photo = $this->saveOptimizedPhoto($request->file('photo'), $driver->photo);
        }

        $driver->name  = $data['name'];
        $driver->phone = $data['phone'] ?? null;
        $driver->email = $data['email'] ?? $driver->email;
        // status non modifiable ici — géré par le livreur depuis son dashboard

        if (! empty($data['password'])) {
            $driver->password             = Hash::make($data['password']);
            $driver->must_change_password = true;
        }

        $driver->save();

        // Synchroniser le compte User lié
        if ($driver->user_id && $driver->user) {
            $userUpdates = ['name' => $data['name'], 'phone' => $data['phone'] ?? null];
            if (! empty($data['email']))    $userUpdates['email'] = $data['email'];
            if (! empty($data['password'])) {
                $userUpdates['password']             = Hash::make($data['password']);
                $userUpdates['must_change_password'] = true;
            }
            $driver->user->update($userUpdates);
        } elseif (! $driver->user_id && ! empty($data['email'])) {
            // Créer le User s'il n'existait pas encore
            $rawPassword = $data['password'] ?? Str::random(10);
            $user = \App\Models\User::create([
                'name'                 => $data['name'],
                'email'                => $data['email'],
                'password'             => Hash::make($rawPassword),
                'role'                 => 'livreur',
                'phone'                => $data['phone'] ?? null,
                'must_change_password' => true,
            ]);
            $driver->user_id = $user->id;
            $driver->save();
        }

        return redirect()->route('company.drivers.index')
            ->with('success', "Chauffeur « {$driver->name} » mis à jour.");
    }

    /* ────────────────────────────────────────────
     |  UPDATE STATUS (toggle rapide depuis l'index)
     ──────────────────────────────────────────── */
    public function updateStatus(Request $request, Driver $driver)
    {
        $company = $this->resolveCompany();
        abort_unless($driver->delivery_company_id === $company->id, 403);

        $request->validate(['status' => 'required|in:available,busy,offline']);

        $driver->update(['status' => $request->status]);

        return back()->with('success', "Statut de « {$driver->name} » mis à jour.");
    }

    /* ────────────────────────────────────────────
     |  RELEASE — libère un driver bloqué en busy sans commandes actives
     ──────────────────────────────────────────── */
    public function release(Driver $driver)
    {
        $company = $this->resolveCompany();
        abort_unless($driver->delivery_company_id === $company->id, 403);

        $hasActive = \App\Models\Order::where('driver_id', $driver->id)
            ->whereIn('status', [\App\Models\Order::STATUS_CONFIRMEE, \App\Models\Order::STATUS_EN_LIVRAISON])
            ->exists();

        if (!$hasActive) {
            $isOnline = $driver->user_id
                ? (bool) \App\Models\User::where('id', $driver->user_id)->value('is_available')
                : false;
            $driver->update(['status' => $isOnline ? 'available' : 'offline']);
        }

        return back()->with('success', "Statut de « {$driver->name} » synchronisé.");
    }

    /* ────────────────────────────────────────────
     |  DESTROY
     ──────────────────────────────────────────── */
    public function destroy(Driver $driver)
    {
        $company = $this->resolveCompany();
        abort_unless($driver->delivery_company_id === $company->id, 403);

        if ($driver->photo) {
            Storage::disk('public')->delete($driver->photo);
        }

        $name = $driver->name;
        $driver->delete();

        return redirect()->route('company.drivers.index')
            ->with('success', "Chauffeur « $name » supprimé.");
    }
}
