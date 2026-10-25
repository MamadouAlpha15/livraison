<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdCampaign;
use App\Models\AdCampaignCreative;
use App\Models\AdPackage;
use App\Support\Campaigns\CampaignAdmin;
use App\Support\Campaigns\CampaignCheckout;
use App\Support\Campaigns\CampaignInbox;
use App\Support\Campaigns\CampaignInvoice;
use App\Support\Campaigns\CampaignNotAllowed;
use App\Support\Campaigns\CampaignPricing;
use App\Support\Campaigns\CampaignResults;
use App\Support\Campaigns\CampaignStateMachine;
use App\Support\Campaigns\InvalidCampaignTransition;
use App\Support\Storefront;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

/**
 * Panneau admin → Campagnes : suivi de toutes les campagnes, actions de l'équipe Shopio
 * (refus, pause, visuels, lancement, statistiques, clôture) et formules.
 * Rôles : superadmin, staff et marketing.
 */
class AdCampaignController extends Controller
{
    /** Filtres de la liste → statuts affichés ('a-traiter' : voir scopeTodo). */
    public const FILTERS = [
        'toutes'      => ['Toutes', null],
        'a-traiter'   => ['À traiter', null],
        'a-payer'     => ['À payer', [AdCampaign::STATUS_A_PAYER]],
        'preparation' => ['Visuels', [AdCampaign::STATUS_PAYEE, AdCampaign::STATUS_EN_PREPARATION, AdCampaign::STATUS_VISUELS_A_VALIDER]],
        'programmees' => ['Programmées', [AdCampaign::STATUS_PROGRAMMEE]],
        'en-cours'    => ['En cours', [AdCampaign::STATUS_EN_COURS, AdCampaign::STATUS_EN_PAUSE]],
        'terminees'   => ['Terminées', [AdCampaign::STATUS_TERMINEE]],
        'arretees'    => ['Refusées et annulées', [AdCampaign::STATUS_REFUSEE, AdCampaign::STATUS_ANNULEE]],
    ];

    /** Visuels envoyés au vendeur depuis plus longtemps : signalés dans la liste. */
    public const CREATIVES_WAIT_HOURS = 48;

    public function __construct(private CampaignAdmin $admin, private CampaignCheckout $checkout) {}

    public function index(Request $request): View
    {
        $filter = array_key_exists($request->query('filtre'), self::FILTERS) ? $request->query('filtre') : 'toutes';
        $type   = in_array($request->query('type'), [AdPackage::TYPE_BOOST, AdPackage::TYPE_SOCIAL], true) ? $request->query('type') : null;
        $search = trim((string) $request->query('q'));

        $campaigns = AdCampaign::with(['shop:id,name', 'package:id,name,type', 'items.product:id,name'])
            ->when($filter === 'a-traiter', fn ($q) => $this->scopeTodo($q))
            ->when(self::FILTERS[$filter][1], fn ($q, $statuses) => $q->whereIn('status', $statuses))
            ->when($type, fn ($q) => $q->where('type', $type))
            ->when($search !== '', fn ($q) => $q->where(fn ($q) => ctype_digit($search)
                ? $q->whereKey((int) $search)->orWhereHas('shop', fn ($s) => $s->where('name', 'like', "%{$search}%"))
                : $q->whereHas('shop', fn ($s) => $s->where('name', 'like', "%{$search}%"))))
            ->latest('id')->paginate(20)->withQueryString();

        return view('admin.campaigns.index', [
            'campaigns' => $campaigns,
            'results'   => CampaignResults::forCampaigns($campaigns->getCollection()),
            'filter'    => $filter,
            'type'      => $type,
            'search'    => $search,
            'alerts'    => $this->alerts(),
            'news'      => CampaignInbox::latest($request->user()),
            'kpis'      => [
                'running' => AdCampaign::where('status', AdCampaign::STATUS_EN_COURS)->count(),
                'todo'    => $this->scopeTodo(AdCampaign::query())->count(),
                'month'   => (int) AdCampaign::whereNotNull('paid_at')->where('paid_at', '>=', now()->startOfMonth())
                    ->whereNotIn('status', [AdCampaign::STATUS_REFUSEE, AdCampaign::STATUS_ANNULEE])->sum('price_gnf'),
            ],
        ]);
    }

    public function show(Request $request, AdCampaign $campaign): View
    {
        CampaignInbox::markCampaignRead($request->user(), $campaign->id);
        $campaign->load([
            'shop.user:id,name,email,phone', 'package', 'items.product',
            'payments' => fn ($q) => $q->latest('id'),
            'creatives' => fn ($q) => $q->orderByDesc('round')->orderBy('id'),
            'stats' => fn ($q) => $q->where('source', 'admin')->orderByDesc('day'),
        ]);
        $results = CampaignResults::forCampaign($campaign);

        return view('admin.campaigns.show', [
            'campaign'     => $campaign,
            'results'      => $results,
            'costPerOrder' => CampaignResults::costPerOrder($campaign, $results['orders']),
            'orders'       => $campaign->orders()->latest()->take(10)->get(),
            'trackingUrl'  => $campaign->isSocial() ? Storefront::publicUrl($campaign->shop) . '?c=' . $campaign->id : null,
            'can'          => [
                'refuse'  => CampaignStateMachine::can($campaign, AdCampaign::STATUS_REFUSEE),
                'pay'     => $campaign->status === AdCampaign::STATUS_A_PAYER,
                'pause'   => $campaign->status === AdCampaign::STATUS_EN_COURS,
                'resume'  => $campaign->status === AdCampaign::STATUS_EN_PAUSE,
                'close'   => CampaignStateMachine::can($campaign, AdCampaign::STATUS_TERMINEE),
                'launch'  => $campaign->status === AdCampaign::STATUS_PROGRAMMEE
                    && ($campaign->isSocial() || $campaign->starts_on->lte(today())),
                'creatives' => $campaign->status === AdCampaign::STATUS_EN_PREPARATION,
                'stats'   => $campaign->isSocial() && $campaign->launched_at !== null,
            ],
            'pendingCount' => $campaign->creatives->where('status', AdCampaignCreative::STATUS_EN_ATTENTE)->count(),
            'statsDays'    => $this->statsDays($campaign),
            'limits'       => ['image' => CampaignAdmin::MAX_IMAGE_KB, 'video' => CampaignAdmin::MAX_VIDEO_KB],
        ]);
    }

    public function readAllNews(Request $request): RedirectResponse
    {
        CampaignInbox::markAllRead($request->user());
        return back();
    }

    public function invoice(AdCampaign $campaign)
    {
        abort_unless(CampaignInvoice::available($campaign), 404);
        return CampaignInvoice::download($campaign);
    }

    public function refuse(Request $request, AdCampaign $campaign): RedirectResponse
    {
        $data = $request->validate(['reason' => ['required', 'string', 'min:5', 'max:500']], [
            'reason.required' => 'Indique le motif du refus : le vendeur le verra.',
            'reason.min'      => 'Indique le motif du refus : le vendeur le verra.',
        ]);
        try {
            $refund = $this->admin->refuse($campaign, $data['reason'], $request->user());
        } catch (InvalidCampaignTransition) {
            return back()->with('error', 'Cette campagne a déjà démarré ou est terminée : elle ne peut plus être refusée.');
        }
        return $this->done($campaign, 'Campagne refusée.' . ($refund > 0 ? ' ' . CampaignPricing::format($refund) . ' remboursés sur le Crédit Shopio de la boutique.' : ''));
    }

    public function markPaid(Request $request, AdCampaign $campaign): RedirectResponse
    {
        $data = $request->validate(['reference' => ['nullable', 'string', 'max:100']]);
        return $this->attempt($campaign, fn () => $this->checkout->markPaidManually($campaign, $request->user(), $data['reference'] ?? null),
            'Paiement manuel enregistré : la campagne est activée.');
    }

    public function pause(AdCampaign $campaign): RedirectResponse
    {
        return $this->attempt($campaign, fn () => $this->admin->pause($campaign), 'Campagne mise en pause. Le vendeur ne peut pas la relancer lui-même.');
    }

    public function resume(AdCampaign $campaign): RedirectResponse
    {
        return $this->attempt($campaign, fn () => $this->admin->resume($campaign), 'Campagne relancée. Les jours de pause sont ajoutés à la fin.');
    }

    public function storeCreatives(Request $request, AdCampaign $campaign): RedirectResponse
    {
        $request->validate([
            'files'   => ['required', 'array', 'min:1', 'max:10'],
            'files.*' => ['file', 'mimetypes:image/jpeg,image/png,image/webp,video/mp4,video/quicktime', 'max:' . CampaignAdmin::MAX_VIDEO_KB],
            'caption' => ['nullable', 'string', 'max:2000'],
        ], [
            'files.required'    => 'Choisis au moins un visuel.',
            'files.*.mimetypes' => 'Formats acceptés : JPG, PNG, WebP, MP4 ou MOV.',
            'files.*.max'       => 'Un fichier dépasse ' . (CampaignAdmin::MAX_VIDEO_KB / 1024) . ' Mo.',
        ]);
        foreach ($request->file('files') as $file) {
            if (!str_starts_with((string) $file->getMimeType(), 'video/') && $file->getSize() > CampaignAdmin::MAX_IMAGE_KB * 1024) {
                return back()->withErrors(['files' => 'Une image dépasse ' . (CampaignAdmin::MAX_IMAGE_KB / 1024) . ' Mo.']);
            }
        }

        try {
            foreach ($request->file('files') as $file) {
                $this->admin->addCreative($campaign, $file, $request->input('caption'));
            }
        } catch (CampaignNotAllowed $e) {
            return back()->with('error', implode(' ', $e->errors()));
        }
        $n = count($request->file('files'));
        return $this->done($campaign, $n . ' visuel' . ($n > 1 ? 's ajoutés' : ' ajouté') . '. Envoie-les au vendeur quand la proposition est complète.');
    }

    public function destroyCreative(AdCampaign $campaign, AdCampaignCreative $creative): RedirectResponse
    {
        abort_unless((int) $creative->ad_campaign_id === (int) $campaign->id, 404);
        return $this->attempt($campaign, fn () => $this->admin->deleteCreative($creative), 'Visuel retiré.');
    }

    public function sendCreatives(AdCampaign $campaign): RedirectResponse
    {
        return $this->attempt($campaign, fn () => $this->admin->sendCreatives($campaign), 'Visuels envoyés au vendeur pour validation.');
    }

    public function launch(Request $request, AdCampaign $campaign): RedirectResponse
    {
        $data = $request->validate(['external_ref' => ['nullable', 'string', 'max:100']]);
        return $this->attempt($campaign, fn () => $this->admin->launch($campaign, $data['external_ref'] ?? null), 'Campagne en ligne.');
    }

    public function storeStats(Request $request, AdCampaign $campaign): RedirectResponse
    {
        $data = $request->validate([
            'day'         => ['required', 'date'],
            'impressions' => ['required', 'integer', 'min:0', 'max:100000000'],
            'spend_gnf'   => ['required', 'integer', 'min:0', 'max:1000000000'],
        ], [
            'impressions.required' => 'Indique le nombre d\'affichages du jour.',
            'spend_gnf.required'   => 'Indique la dépense du jour en GNF.',
        ]);
        return $this->attempt($campaign, fn () => $this->admin->recordStats($campaign, Carbon::parse($data['day']), (int) $data['impressions'], (int) $data['spend_gnf']),
            'Statistiques du ' . Carbon::parse($data['day'])->translatedFormat('j F') . ' enregistrées.');
    }

    public function close(AdCampaign $campaign): RedirectResponse
    {
        return $this->attempt($campaign, fn () => $this->admin->close($campaign), 'Campagne clôturée. Le vendeur voit son bilan sur sa page Campagnes.');
    }

    public function packages(): View
    {
        return view('admin.campaigns.packages', [
            'packages' => AdPackage::orderBy('sort_order')->get()->groupBy('type'),
            'usage'    => AdCampaign::whereIn('status', AdCampaign::RESERVING_STATUSES)
                ->selectRaw('ad_package_id, COUNT(*) AS n')->groupBy('ad_package_id')->pluck('n', 'ad_package_id'),
        ]);
    }

    public function updatePackage(Request $request, AdPackage $package): RedirectResponse
    {
        $rules = ['name' => ['required', 'string', 'max:60'], 'is_active' => ['nullable', 'boolean']];
        if ($package->isSocial()) {
            $rules += ['ad_budget_gnf' => ['required', 'integer', 'min:0', 'max:1000000000'], 'fee_gnf' => ['required', 'integer', 'min:0', 'max:1000000000']];
        } else {
            $rules['max_actives'] = ['required', 'integer', 'min:1', 'max:100'];
            foreach ($package->durations() as $days) {
                $rules["prices.{$days}"] = ['required', 'integer', 'min:1000', 'max:1000000000'];
            }
        }
        $data = $request->validate($rules, [
            'prices.*.required' => 'Indique un prix pour chaque durée.',
            'prices.*.min'      => 'Un prix doit être d\'au moins 1 000 GNF.',
        ]);

        $this->admin->updatePackage($package, $data);
        return redirect()->route('admin.campaigns.packages')->with('success', 'Formule « ' . $package->name . ' » enregistrée. Les nouveaux prix valent pour les prochaines commandes.');
    }

    /** Campagnes qui attendent une action de l'équipe. */
    private function scopeTodo(Builder $query): Builder
    {
        return $query->where(fn ($q) => $q
            ->whereIn('status', [AdCampaign::STATUS_PAYEE, AdCampaign::STATUS_EN_PREPARATION])
            ->orWhere(fn ($q) => $q->where('status', AdCampaign::STATUS_PROGRAMMEE)->where('type', AdPackage::TYPE_SOCIAL)
                ->whereDate('starts_on', '<=', today()->toDateString()))
            ->orWhere(fn ($q) => $q->whereIn('status', [AdCampaign::STATUS_EN_COURS, AdCampaign::STATUS_EN_PAUSE])
                ->whereDate('ends_on', '<', today()->toDateString())));
    }

    /** Compteurs affichés en haut de la liste : ce qui demande une action. */
    private function alerts(): array
    {
        $count = fn (callable $scope) => $scope(AdCampaign::query())->count();
        return array_filter([
            'preparer' => $count(fn ($q) => $q->whereIn('status', [AdCampaign::STATUS_PAYEE, AdCampaign::STATUS_EN_PREPARATION])),
            'lancer'   => $count(fn ($q) => $q->where('status', AdCampaign::STATUS_PROGRAMMEE)->where('type', AdPackage::TYPE_SOCIAL)
                ->whereDate('starts_on', '<=', today()->toDateString())),
            'cloturer' => $count(fn ($q) => $q->whereIn('status', [AdCampaign::STATUS_EN_COURS, AdCampaign::STATUS_EN_PAUSE])
                ->whereDate('ends_on', '<', today()->toDateString())),
            'vendeur'  => $count(fn ($q) => $q->where('status', AdCampaign::STATUS_VISUELS_A_VALIDER)
                ->where('creatives_sent_at', '<=', now()->subHours(self::CREATIVES_WAIT_HOURS))),
        ]);
    }

    /** Jours pour lesquels l'équipe peut saisir des statistiques (du début à aujourd'hui ou la fin). */
    private function statsDays(AdCampaign $campaign): array
    {
        if (!$campaign->isSocial() || !$campaign->launched_at) {
            return [];
        }
        $days = [];
        $last = $campaign->ends_on->copy()->min(today());
        for ($d = $last->copy(); $d->gte($campaign->starts_on); $d = $d->copy()->subDay()) {
            $days[$d->toDateString()] = $d->translatedFormat('l j F');
        }
        return $days;
    }

    private function attempt(AdCampaign $campaign, callable $action, string $success): RedirectResponse
    {
        try {
            $action();
        } catch (CampaignNotAllowed $e) {
            return back()->with('error', implode(' ', $e->errors()));
        } catch (InvalidCampaignTransition) {
            return back()->with('error', 'Cette action n\'est pas possible dans l\'état actuel de la campagne.');
        }
        return $this->done($campaign, $success);
    }

    private function done(AdCampaign $campaign, string $message): RedirectResponse
    {
        return redirect()->route('admin.campaigns.show', $campaign)->with('success', $message);
    }
}
