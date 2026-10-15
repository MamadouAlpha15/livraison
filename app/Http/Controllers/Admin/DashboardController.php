<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DeliveryCompany;
use App\Models\Driver;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Review;
use App\Models\Shop;
use App\Models\Subscription;
use App\Models\SupportTicket;
use App\Models\User;

class DashboardController extends Controller
{
    public function index()
    {
        $pendingCompanies = DeliveryCompany::where('approved', false)->latest()->paginate(5);
        $kpis     = $this->buildKpis();
        $chart    = $this->buildChart();
        $activity = $this->buildActivity();
        $allShops     = Shop::with('owner')->latest()->get(['id','name','user_id','created_at','is_approved']);
        $allCompanies = DeliveryCompany::latest()->get(['id','name','user_id','created_at','approved']);
        $sys          = $this->buildSystemStatus();

        return view('dashboards.SuperAdmin', compact('pendingCompanies', 'kpis', 'chart', 'activity', 'allShops', 'allCompanies', 'sys'));
    }

    /**
     * Statut système RÉEL (remplace l'ancienne liste codée en dur qui affichait
     * toujours "Actif"/"Connectée" quel que soit l'état réel — trompeur).
     */
    private function buildSystemStatus(): array
    {
        $dbOk = true;
        try {
            \DB::select('select 1');
        } catch (\Throwable $e) {
            $dbOk = false;
        }

        $cacheDriver = config('cache.default', 'file');
        $cacheOk     = true;
        try {
            \Illuminate\Support\Facades\Cache::put('__sys_status_check', 1, 5);
            $cacheOk = \Illuminate\Support\Facades\Cache::get('__sys_status_check') === 1;
        } catch (\Throwable $e) {
            $cacheOk = false;
        }

        $sandbox   = (bool) config('chapchappay.sandbox', true);
        $mailHost  = config('mail.mailers.smtp.host');
        $mailOk    = filled($mailHost);
        $googleOk  = filled(config('services.google.client_id'));
        $exportsOk = class_exists(\Maatwebsite\Excel\Facades\Excel::class) && class_exists(\Barryvdh\DomPDF\Facade\Pdf::class);

        return [
            ['n' => 'Application Laravel', 'c' => 'g', 'l' => 'En ligne'],
            ['n' => 'Base de données',     'c' => $dbOk ? 'g' : 'r', 'l' => $dbOk ? 'Connectée' : 'Injoignable'],
            ['n' => 'Cache (' . ucfirst($cacheDriver) . ')', 'c' => $cacheOk ? 'g' : 'r', 'l' => $cacheOk ? 'Actif' : 'Hors service'],
            ['n' => 'Paiements (ChapChap Pay)', 'c' => $sandbox ? 'a' : 'g', 'l' => $sandbox ? 'Sandbox (test)' : 'Production'],
            ['n' => 'Emails (SMTP)', 'c' => $mailOk ? 'g' : 'a', 'l' => $mailOk ? 'Configuré' : 'À configurer'],
            ['n' => 'Google OAuth', 'c' => $googleOk ? 'g' : 'a', 'l' => $googleOk ? 'Actif' : 'À configurer'],
            ['n' => 'PDF / Excel', 'c' => $exportsOk ? 'g' : 'r', 'l' => $exportsOk ? 'Opérationnel' : 'Indisponible'],
        ];
    }

    public function stats()
    {
        return response()->json([
            'kpis'     => $this->buildKpis(),
            'chart'    => $this->buildChart(),
            'activity' => $this->buildActivity(),
            'ts'       => now()->format('H:i:s'),
        ]);
    }

    public function approveCompany(DeliveryCompany $company)
    {
        $company->update(['approved' => true, 'approved_at' => now()]);
        return back()->with('success', "L'entreprise {$company->name} a été approuvée.");
    }

    private function buildKpis(): array
    {
        $usersTotal        = User::count();
        $usersToday        = User::whereDate('created_at', today())->count();
        $shopsTotal        = Shop::count();
        $shopsActive       = Shop::where('is_approved', true)->count();
        $shopsPending      = Shop::where('is_approved', false)->count();
        $shopsApprovedToday= Shop::where('is_approved', true)->whereDate('updated_at', today())->count();
        $compTotal         = DeliveryCompany::count();
        $compActive        = DeliveryCompany::where('approved', true)->count();
        $compPending       = DeliveryCompany::where('approved', false)->count();
        $compApprovedToday = DeliveryCompany::where('approved', true)
                                ->whereDate('approved_at', today())->count();
        $driversTotal      = Driver::count();
        $ordersToday       = Order::whereDate('created_at', today())->count();
        $ordersTotal       = Order::count();
        $ordersDelivered   = Order::where('status', 'livrée')->count();
        $pendingAppr       = $compPending; // alias gardé pour compatibilité
        // ── Revenus plateforme : boutiques (total commandes) + livraison (delivery_fee)
        $mergeRev = function (int $month, int $year): array {
            $byDev = [];

            // CA boutiques (commandes livrées)
            $rows = \DB::table('orders')
                ->join('shops', 'orders.shop_id', '=', 'shops.id')
                ->where('orders.status', 'livrée')
                ->whereMonth('orders.created_at', $month)
                ->whereYear('orders.created_at', $year)
                ->selectRaw('COALESCE(shops.currency,"GNF") as dev, SUM(orders.total) as t')
                ->groupBy('dev')
                ->get();
            foreach ($rows as $r) {
                $byDev[$r->dev] = ($byDev[$r->dev] ?? 0) + (float) $r->t;
            }

            // Frais livraison (delivery_fee des commandes livrées)
            $rows2 = \DB::table('orders')
                ->join('delivery_companies', 'orders.delivery_company_id', '=', 'delivery_companies.id')
                ->where('orders.status', 'livrée')
                ->whereNotNull('orders.delivery_company_id')
                ->whereMonth('orders.created_at', $month)
                ->whereYear('orders.created_at', $year)
                ->selectRaw('COALESCE(delivery_companies.currency,"GNF") as dev, SUM(orders.delivery_fee) as t')
                ->groupBy('dev')
                ->get();
            foreach ($rows2 as $r) {
                $byDev[$r->dev] = ($byDev[$r->dev] ?? 0) + (float) $r->t;
            }

            arsort($byDev);
            return $byDev;
        };

        $revByCurrency     = $mergeRev(now()->month, now()->year);
        $revLastByCurrency = $mergeRev(now()->subMonth()->month, now()->subMonth()->year);
        $revMonth          = array_sum($revByCurrency);    // total toutes devises (pour trend)
        $revLastMonth      = array_sum($revLastByCurrency);
        $revTotal          = (float) Payment::where('status', 'payé')->sum('amount');
        $avgRating         = round((float)(Review::avg('rating') ?? 0), 1);
        $openTickets       = SupportTicket::where('status', 'open')->count();
        $adminIds          = User::where('role', 'superadmin')->pluck('id');
        $unreadMessages    = (int) \DB::table('support_messages')
            ->join('support_tickets', 'support_tickets.id', '=', 'support_messages.ticket_id')
            ->where('support_tickets.status', 'open')
            ->whereNotIn('support_messages.user_id', $adminIds)
            ->count();
        $clientsTotal      = User::where('role', 'client')->count();
        $clientsToday      = User::where('role', 'client')->whereDate('created_at', today())->count();
        $paidToday         = Payment::where('status', 'payé')->whereDate('created_at', today())->count();

        // ── Revenus abonnements (Boutiques Pro + Entreprises Business)
        // CA = tous les paiements reçus (active + expired) → argent déjà encaissé
        // Compteurs = uniquement actifs → nombre réel d'abonnés
        $subCaTotal    = (int) Subscription::whereIn('status', ['active', 'expired'])->sum('amount');
        $subCaToday    = (int) Subscription::whereIn('status', ['active', 'expired'])->whereDate('created_at', today())->sum('amount');
        $subCountToday = (int) Subscription::where('status', 'active')->whereDate('created_at', today())->count();
        $subCountMonth = (int) Subscription::where('status', 'active')->whereMonth('created_at', now()->month)->whereYear('created_at', now()->year)->count();

        return compact(
            'usersTotal', 'usersToday',
            'shopsTotal', 'shopsActive', 'shopsPending', 'shopsApprovedToday',
            'compTotal', 'compActive', 'compPending', 'compApprovedToday',
            'driversTotal',
            'ordersToday', 'ordersTotal', 'ordersDelivered',
            'pendingAppr',
            'revMonth', 'revLastMonth', 'revByCurrency', 'revTotal',
            'avgRating',
            'openTickets', 'unreadMessages',
            'clientsTotal', 'clientsToday',
            'paidToday',
            'subCaTotal', 'subCaToday', 'subCountToday', 'subCountMonth'
        );
    }

    private function buildChart(): array
    {
        // 2 requêtes groupées au lieu de 14 (une paire par jour dans une boucle) —
        // même logique, même résultat, juste calculé en une passe par la base de données.
        $start = now()->subDays(6)->startOfDay();
        $end   = now()->endOfDay();

        $ordersByDay = Order::selectRaw('DATE(created_at) as d, COUNT(*) as c')
            ->whereBetween('created_at', [$start, $end])
            ->groupBy('d')
            ->pluck('c', 'd');

        $deliveriesByDay = Order::selectRaw('DATE(created_at) as d, COUNT(*) as c')
            ->whereBetween('created_at', [$start, $end])
            ->whereNotNull('livreur_id')
            ->groupBy('d')
            ->pluck('c', 'd');

        $rows = [];
        for ($i = 6; $i >= 0; $i--) {
            $date   = now()->subDays($i);
            $key    = $date->toDateString();
            $rows[] = [
                'label'      => ucfirst($date->locale('fr')->isoFormat('ddd')),
                'orders'     => (int) ($ordersByDay[$key] ?? 0),
                'deliveries' => (int) ($deliveriesByDay[$key] ?? 0),
            ];
        }
        return $rows;
    }

    private function buildActivity(): array
    {
        $items = [];

        // Nouveaux inscrits 48h
        User::whereDate('created_at', '>=', now()->subHours(48))
            ->latest()->limit(4)->get()
            ->each(fn($u) => $items[] = [
                'c' => 'p', 'ic' => '👤',
                't' => "Nouvel inscrit : {$u->name}",
                'ts' => $u->created_at,
            ]);

        // Commandes récentes
        Order::with('shop')->latest()->limit(5)->get()
            ->each(fn($o) => $items[] = [
                'c' => 'a', 'ic' => '📦',
                't' => 'Commande #'.str_pad($o->id, 4, '0', STR_PAD_LEFT).' — '.($o->shop?->name ?? '?'),
                'ts' => $o->created_at,
            ]);

        // Entreprises enregistrées récemment
        DeliveryCompany::latest()->limit(3)->get()
            ->each(fn($c) => $items[] = [
                'c' => 'b', 'ic' => '🚚',
                't' => "Entreprise enregistrée : {$c->name}",
                'ts' => $c->created_at,
            ]);

        // Paiements encaissés récents
        Payment::where('status', 'payé')->latest()->limit(4)->get()
            ->each(fn($p) => $items[] = [
                'c' => 'g', 'ic' => '💳',
                't' => 'Paiement encaissé — '.number_format($p->amount, 0, ',', ' ')
                       .' (cmd #'.str_pad($p->order_id ?? 0, 4, '0', STR_PAD_LEFT).')',
                'ts' => $p->created_at,
            ]);

        // Boutiques récentes
        Shop::latest()->limit(3)->get()
            ->each(fn($s) => $items[] = [
                'c' => 'g', 'ic' => '🏪',
                't' => "Boutique créée : {$s->name}",
                'ts' => $s->created_at,
            ]);

        usort($items, fn($a, $b) => ($b['ts'] ?? now()) <=> ($a['ts'] ?? now()));

        return array_map(fn($item) => [
            'c'  => $item['c'],
            'ic' => $item['ic'],
            't'  => $item['t'],
            'h'  => $item['ts'] ? $item['ts']->diffForHumans() : '—',
        ], array_slice($items, 0, 9));
    }
}
