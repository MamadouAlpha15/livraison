<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AiToolCall;
use App\Models\AiUsage;
use App\Models\FaqEntry;
use App\Models\ShopioAiSetting;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/**
 * Équipe Shopio → Shopio IA : FAQ lue par l'IA, réglages (activation, messages par mois selon le plan),
 * coût par boutique et par mois, journal des outils appelés.
 */
class ShopioAiController extends Controller
{
    public function index(Request $request)
    {
        $months = AiUsage::query()->distinct()->orderByDesc('month')->pluck('month');
        $month = $months->contains($request->query('mois')) ? $request->query('mois') : now()->format('Y-m');

        $usages = AiUsage::with('shop:id,name,plan,plan_expires_at')->where('month', $month)->orderByDesc('cost_micro_usd')->get();

        return view('admin.shopio-ai.index', [
            'settings' => ShopioAiSetting::current(),
            'faq'      => FaqEntry::orderBy('category')->orderBy('position')->get(),
            'months'   => $months->push(now()->format('Y-m'))->unique()->sortDesc()->values(),
            'month'    => $month,
            'usages'   => $usages,
            'totals'   => [
                'messages' => $usages->sum('messages'),
                'handoffs' => $usages->sum('handoffs'),
                'cost'     => $usages->sum('cost_micro_usd') / 1_000_000,
            ],
            'calls'    => AiToolCall::with('shop:id,name')->latest('id')->limit(40)->get(),
        ]);
    }

    public function updateSettings(Request $request)
    {
        $data = $request->validate([
            'free_monthly_limit' => ['required', 'integer', 'between:0,100000'],
            'pro_monthly_limit'  => ['required', 'integer', 'between:0,100000'],
        ]);
        $data['enabled'] = $request->boolean('enabled');
        $data['free_auto_allowed'] = $request->boolean('free_auto_allowed');
        ShopioAiSetting::current()->update($data);
        return back()->with('success', 'Réglages de Shopio IA enregistrés.');
    }

    public function storeFaq(Request $request)
    {
        $data = $this->validateFaq($request);
        $data['position'] = (int) FaqEntry::where('category', $data['category'])->max('position') + 1;
        FaqEntry::create($data);
        return back()->with('success', 'Question ajoutée à la FAQ. Shopio IA l\'utilise dès maintenant.');
    }

    public function updateFaq(Request $request, FaqEntry $entry)
    {
        $entry->update($this->validateFaq($request));
        return back()->with('success', 'Question mise à jour.');
    }

    public function destroyFaq(FaqEntry $entry)
    {
        $entry->delete();
        return back()->with('success', 'Question supprimée de la FAQ.');
    }

    private function validateFaq(Request $request): array
    {
        $data = $request->validate([
            'category' => ['required', Rule::in(array_keys(FaqEntry::CATEGORIES))],
            'question' => ['required', 'string', 'max:255'],
            'answer'   => ['required', 'string', 'max:2000'],
        ]);
        // Nouvelle question : active ; modification : selon la case « Utilisée par Shopio IA »
        $data['is_active'] = $request->routeIs('admin.shopio-ai.faq.store') || $request->boolean('is_active');
        return $data;
    }
}
