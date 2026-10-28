<?php

namespace App\Http\Controllers\Boutique;

use App\Http\Controllers\Controller;
use App\Http\Requests\FollowUpRuleRequest;
use App\Models\FollowUp;
use App\Models\FollowUpRule;
use App\Models\PromoCode;
use App\Models\Shop;
use App\Services\FollowUp\FollowUpCatalog as Cat;
use App\Services\FollowUp\TemplateRenderer;
use App\Services\FollowUpService;
use App\Services\ShopioAI\FollowUpWriter;
use App\Support\Ai\AiUnavailable;
use Illuminate\Http\Request;

/**
 * Espace vendeur → Relances : situations (activer, régler), aperçu, « Générer avec Shopio IA »,
 * historique et statistiques. Le vendeur ne voit et ne règle que sa boutique.
 */
class FollowUpController extends Controller
{
    public function __construct(private FollowUpService $followUps) {}

    public function index(Request $request)
    {
        $shop = $this->shop($request);
        $rules = collect(Cat::vendorTypes())->map(fn ($meta, $type) => ['meta' => $meta, 'rule' => FollowUpRule::resolve($shop->id, $type)]);

        $history = FollowUp::with(['user:id,name', 'product:id,name', 'shopMessage:id,read_at'])
            ->where('shop_id', $shop->id)->where('type', '!=', Cat::INACTIVE_VENDOR)
            ->whereIn('status', [FollowUp::STATUS_SENT, FollowUp::STATUS_STOPPED])
            ->when($request->filled('client'), fn ($q) => $q->where('user_id', $request->integer('client')))
            ->latest('updated_at')->paginate(15)->withQueryString();

        return view('boutique.followups.index', [
            'shop'    => $shop,
            'rules'   => $rules,
            'stats'   => $this->followUps->stats($shop->id),
            'history' => $history,
        ]);
    }

    public function edit(Request $request, string $type)
    {
        $shop = $this->shop($request);
        abort_unless(isset(Cat::vendorTypes()[$type]), 404);
        $rule = FollowUpRule::resolve($shop->id, $type);

        return view('boutique.followups.edit', [
            'shop'    => $shop,
            'type'    => $type,
            'meta'    => Cat::get($type),
            'rule'    => $rule,
            'promos'  => PromoCode::where('shop_id', $shop->id)->where('is_active', true)->orderBy('code')->get(['id', 'code', 'expires_at']),
            'preview' => TemplateRenderer::render($rule->effectiveTemplate(), $this->followUps->sampleVariables($shop, $rule)),
        ]);
    }

    public function update(FollowUpRuleRequest $request, string $type)
    {
        $shop = $this->shop($request);
        abort_unless(isset(Cat::vendorTypes()[$type]), 404);
        FollowUpRule::updateOrCreate(['shop_id' => $shop->id, 'type' => $type], $request->ruleData());

        return redirect()->route('boutique.followups.index')->with('success', 'Relance « ' . Cat::get($type)['label'] . ' » enregistrée.');
    }

    public function toggle(Request $request, string $type)
    {
        $shop = $this->shop($request);
        abort_unless(isset(Cat::vendorTypes()[$type]), 404);
        $rule = FollowUpRule::resolve($shop->id, $type);
        $rule->enabled = !$rule->enabled;
        $rule->save();

        return back()->with('success', 'Relance « ' . Cat::get($type)['label'] . ' » ' . ($rule->enabled ? 'activée.' : 'désactivée.'));
    }

    /** Aperçu du message avec des données d'exemple (texte, ton, langue et code promo du formulaire) */
    public function preview(Request $request, string $type)
    {
        $shop = $this->shop($request);
        abort_unless(isset(Cat::types()[$type]), 404);
        $data = $request->validate([
            'template'      => ['nullable', 'string', 'max:1000'],
            'tone'          => ['nullable', 'string'],
            'locale'        => ['nullable', 'string'],
            'promo_code_id' => ['nullable', 'integer'],
        ]);

        $rule = FollowUpRule::defaults($shop->id, $type);
        $rule->fill([
            'template'      => $data['template'] ?? null,
            'tone'          => isset(Cat::TONES[$data['tone'] ?? '']) ? $data['tone'] : 'amical',
            'locale'        => isset(Cat::LOCALES[$data['locale'] ?? '']) ? $data['locale'] : 'fr',
            'promo_code_id' => PromoCode::where('shop_id', $shop->id)->whereKey($data['promo_code_id'] ?? 0)->value('id'),
        ]);
        $text = TemplateRenderer::render($rule->effectiveTemplate(), $this->followUps->sampleVariables($shop, $rule));
        if (Cat::isClientFacing($type)) {
            $text .= "\n\n" . Cat::stopFooter($rule->locale);
        }

        return response()->json([
            'preview'  => $text,
            'unknown'  => TemplateRenderer::unknownVariables((string) ($data['template'] ?? '')),
            'template' => $rule->effectiveTemplate(),
        ]);
    }

    /** « Générer avec Shopio IA » : un message proposé, à relire avant d'enregistrer */
    public function generate(Request $request, string $type, FollowUpWriter $writer)
    {
        $shop = $this->shop($request);
        abort_unless(isset(Cat::vendorTypes()[$type]), 404);
        $data = $request->validate([
            'tone'   => ['required', 'in:' . implode(',', array_keys(Cat::TONES))],
            'locale' => ['required', 'in:' . implode(',', array_keys(Cat::LOCALES))],
            'wish'   => ['nullable', 'string', 'max:200'],
        ]);

        try {
            return response()->json(['template' => $writer->write($shop, $type, $data['tone'], $data['locale'], $data['wish'] ?? null)]);
        } catch (AiUnavailable $e) {
            return response()->json(['error' => $e->getMessage()], 503);
        }
    }

    private function shop(Request $request): Shop
    {
        $shop = $request->user()->shop_id ? Shop::find($request->user()->shop_id) : null;
        abort_unless($shop && (int) $shop->user_id === (int) $request->user()->id, 403);
        return $shop;
    }
}
