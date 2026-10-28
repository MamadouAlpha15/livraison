<?php

use App\Models\AiConversationState;
use App\Models\AiReplySuggestion;
use App\Models\AiToolCall;
use App\Models\AiUsage;
use App\Models\Order;
use App\Models\ShopAiSetting;
use App\Models\ShopioAiSetting;
use App\Models\ShopMessage;
use App\Models\User;
use App\Services\PushService;
use App\Services\ShopioAI\AiContext;
use App\Services\ShopioAI\ChatModel;
use App\Services\ShopioAI\ChatResult;
use App\Services\ShopioAI\ShopioAssistant;
use App\Services\ShopioAI\ShopioTools;
use App\Support\Ai\AiUnavailable;
use Tests\Support\CampaignFixtures as F;

beforeEach(function () {
    // Modèle simulé : $this->model->script reçoit l'exécuteur d'outils et renvoie la réponse finale
    $this->model = new class implements ChatModel {
        public $script;
        public array $calls = [];
        public function run(string $system, array $messages, array $tools, callable $executeTool): ChatResult
        {
            $this->calls[] = compact('system', 'messages', 'tools');
            return ($this->script)($executeTool, $messages);
        }
    };
    $this->model->script = fn () => new ChatResult('Bonjour !', 100, 20);
    app()->instance(ChatModel::class, $this->model);
    $this->push = new class {
        public array $sent = [];
        public function sendToUser(User $user, string $title, string $body, int $badge = 0, string $url = '/'): void { $this->sent[] = [$user->id, $title]; }
    };
    app()->instance(PushService::class, $this->push);

    $this->shop = F::shop();
    $this->shop->update(['name' => 'Chez Fanta', 'currency' => 'GNF', 'plan' => 'pro', 'plan_expires_at' => now()->addMonth()]);
    $this->vendor = $this->shop->owner;
    $this->product = F::product($this->shop, ['name' => 'Robe en wax', 'price' => 150000, 'stock' => 3]);
    $this->client = User::factory()->create(['role' => 'client', 'name' => 'Aïssatou Bah']);
    $this->settings = ShopAiSetting::create(['shop_id' => $this->shop->id, 'enabled' => true, 'mode' => 'auto', 'address_form' => 'tu']);
});

/** Le client écrit dans la messagerie de la boutique (l'IA répond via le job, synchrone en test) */
function clientWrites($test, string $body, ?User $client = null): ShopMessage
{
    return ShopMessage::create(['shop_id' => $test->shop->id, 'product_id' => $test->product->id, 'sender_id' => ($client ?? $test->client)->id,
        'receiver_id' => $test->vendor->id, 'type' => 'text', 'body' => $body]);
}

function aiReplies($test): \Illuminate\Support\Collection
{
    return ShopMessage::where('shop_id', $test->shop->id)->where('is_ai', true)->orderBy('id')->pluck('body');
}

function ctx($test, ?User $client = null): AiContext
{
    return new AiContext($test->shop, $client ?? $test->client, $test->settings, $test->product, null);
}

/* ── Outils et permissions ── */

test('statut_commande et suivi_livraison : un client ne voit que ses propres commandes', function () {
    $tools = app(ShopioTools::class);
    $mine = Order::forceCreate(['user_id' => $this->client->id, 'shop_id' => $this->shop->id, 'total' => 150000, 'status' => Order::STATUS_EN_LIVRAISON]);
    $other = Order::forceCreate(['user_id' => User::factory()->create(['role' => 'client'])->id, 'shop_id' => $this->shop->id, 'total' => 99000, 'status' => Order::STATUS_LIVREE]);

    expect($tools->execute('statut_commande', ['commande_id' => $other->id], ctx($this)))->toBe(['erreur' => 'Aucune commande de ce client avec ce numéro.'])
        ->and($tools->execute('suivi_livraison', ['commande_id' => $other->id], ctx($this)))->toHaveKey('erreur')
        ->and($tools->execute('statut_commande', [], ctx($this))['commandes'])->toHaveCount(1)
        ->and($tools->execute('statut_commande', ['commande_id' => $mine->id], ctx($this))['commandes'][0]['montant'])->toBe("150\u{00A0}000\u{00A0}GNF")
        ->and($tools->execute('suivi_livraison', ['commande_id' => $mine->id], ctx($this)))->toMatchArray(['statut' => 'en_livraison', 'livreur' => 'pas encore assigné']);

    // Visiteur non connecté : aucune commande
    expect($tools->execute('statut_commande', [], new AiContext($this->shop, null, $this->settings)))->toHaveKey('erreur');
});

test('produits : seulement ceux en ligne de cette boutique, avec prix et stock réels', function () {
    $tools = app(ShopioTools::class);
    F::product($this->shop, ['name' => 'Robe cachée', 'is_active' => false]);
    $elsewhere = F::product(F::shop(), ['name' => 'Robe ailleurs']);

    $found = $tools->execute('rechercher_produits', ['texte' => 'robe'], ctx($this))['produits'];
    expect(collect($found)->pluck('nom')->all())->toBe(['Robe en wax'])
        ->and($found[0])->toMatchArray(['prix' => "150\u{00A0}000\u{00A0}GNF", 'stock' => '3 en stock'])
        ->and($tools->execute('details_produit', ['produit_id' => $elsewhere->id], ctx($this)))->toHaveKey('erreur')
        ->and($tools->execute('details_produit', ['produit_id' => $this->product->id], ctx($this))['nom'])->toBe('Robe en wax');
});

test('infos boutique, codes promo actifs et FAQ viennent des données ; « non renseigné » sinon', function () {
    $tools = app(ShopioTools::class);
    $this->settings->update(['delivery_info' => 'Livraison à Conakry : 20 000 GNF, sous 48 h.']);
    \App\Models\PromoCode::forceCreate(['shop_id' => $this->shop->id, 'code' => 'WAX10', 'type' => 'percent', 'value' => 10, 'is_active' => true]);
    \App\Models\PromoCode::forceCreate(['shop_id' => $this->shop->id, 'code' => 'VIEUX', 'type' => 'percent', 'value' => 5, 'is_active' => true, 'expires_at' => now()->subDay()]);

    expect($tools->execute('infos_boutique', [], ctx($this)))->toMatchArray(['livraison' => 'Livraison à Conakry : 20 000 GNF, sous 48 h.', 'retours' => 'non renseigné par la boutique'])
        ->and(collect($tools->execute('codes_promo_actifs', [], ctx($this))['codes'])->pluck('code')->all())->toBe(['WAX10'])
        ->and(collect($tools->execute('faq_shopio', ['sujet' => 'paiement'], ctx($this))['questions'])->pluck('reponse')->implode(' '))->toContain('espèces au livreur');
});

test('chaque appel d\'outil est journalisé, sans données personnelles', function () {
    app(ShopioTools::class)->execute('statut_commande', ['commande_id' => 999, 'telephone' => '622458137'], ctx($this));
    $log = AiToolCall::sole();
    expect($log->tool)->toBe('statut_commande')->and($log->input)->toBe(['commande_id' => 999])->and($log->ok)->toBeFalse()
        ->and($log->user_id)->toBe($this->client->id);
});

/* ── Réponses ── */

test('mode automatique : réponse marquée Shopio IA, avec le vrai prix lu par l\'outil', function () {
    $this->model->script = function ($exec) {
        $p = $exec('details_produit', ['produit_id' => $this->product->id]);
        return new ChatResult("Oui ! La {$p['nom']} est à {$p['prix']}, il en reste 3.", 1200, 80);
    };

    clientWrites($this, 'slt la robe c combien');

    expect(aiReplies($this)->all())->toBe(["Oui ! La Robe en wax est à 150\u{00A0}000\u{00A0}GNF, il en reste 3."])
        ->and($this->model->calls[0]['system'])->toContain('N\'invente jamais')->toContain('Tutoie')
        ->and($this->model->calls[0]['messages'][0]['content'])->toContain('Client : slt la robe c combien')
        ->and(AiUsage::sole()->only(['messages', 'input_tokens', 'output_tokens', 'cost_micro_usd']))
        ->toBe(['messages' => 1, 'input_tokens' => 1200, 'output_tokens' => 80, 'cost_micro_usd' => 1200 * 4 + 80 * 20]);
});

test('l\'IA ne donne jamais un prix ou un stock absent des données : réponse bloquée et transfert au vendeur', function () {
    $this->model->script = function ($exec) {
        $exec('details_produit', ['produit_id' => $this->product->id]);
        return new ChatResult('Elle est à 90 000 GNF et il en reste 12 en stock.', 500, 30);
    };

    clientWrites($this, 'combien la robe ?');

    expect(aiReplies($this)->all())->toBe([ShopioAssistant::VERIFY])
        ->and(AiConversationState::where('client_id', $this->client->id)->sole()->handed_off_at)->not->toBeNull()
        ->and(collect($this->push->sent)->pluck(1)->all())->toContain('Shopio IA te passe la main');

    // Sans outil appelé, un prix cité est aussi bloqué
    expect(\App\Services\ShopioAI\ReplyGuard::unsupported('Livraison à 25 000 GNF', [], ''))->toBe(['25 000'])
        ->and(\App\Services\ShopioAI\ReplyGuard::unsupported('-10 % avec WAX10', [['codes' => [['code' => 'WAX10', 'reduction' => '10 %']]]], ''))->toBe([]);
});

test('transfert au vendeur : l\'IA se tait ensuite dans cette conversation', function () {
    $this->model->script = function ($exec) {
        $exec('transferer_au_vendeur', ['raison' => 'client mécontent, veut un remboursement']);
        return new ChatResult('Je transmets ta demande au vendeur, il te répond très vite.', 300, 20);
    };
    clientWrites($this, 'je veux être remboursé !!');

    expect(AiConversationState::sole()->handoff_reason)->toBe('client mécontent, veut un remboursement')
        ->and(AiUsage::sole()->handoffs)->toBe(1);

    $this->model->script = fn () => new ChatResult('Encore moi', 10, 5);
    clientWrites($this, 'allo ?');
    expect(aiReplies($this))->toHaveCount(1);
});

test('mode suggestion : rien n\'est envoyé au client, le vendeur reçoit une réponse à valider', function () {
    $this->settings->update(['mode' => 'suggestion']);
    $this->model->script = fn () => new ChatResult('Bonjour, oui elle est disponible.', 100, 10);

    clientWrites($this, 'elle est dispo ?');

    expect(aiReplies($this))->toHaveCount(0)
        ->and(AiReplySuggestion::sole()->only(['body', 'status', 'client_id']))->toBe(['body' => 'Bonjour, oui elle est disponible.', 'status' => 'pending', 'client_id' => $this->client->id])
        ->and(collect($this->push->sent)->pluck(1)->all())->toContain('Shopio IA a préparé une réponse');
});

test('plan gratuit : suggestions seulement, et plus rien quand la limite du mois est atteinte', function () {
    $this->shop->update(['plan' => 'free', 'plan_expires_at' => null]);
    ShopioAiSetting::current()->update(['free_monthly_limit' => 1]);

    clientWrites($this, 'bonjour');
    expect(aiReplies($this))->toHaveCount(0)->and(AiReplySuggestion::count())->toBe(1);

    clientWrites($this, 'vous êtes là ?');
    expect(AiReplySuggestion::count())->toBe(1);
});

test('le vendeur écrit lui-même : l\'IA se met en pause 2 h dans cette conversation', function () {
    ShopMessage::create(['shop_id' => $this->shop->id, 'sender_id' => $this->vendor->id, 'receiver_id' => $this->client->id, 'type' => 'text', 'body' => 'Bonjour, je suis là']);
    clientWrites($this, 'ok merci');
    expect(aiReplies($this))->toHaveCount(0);

    $this->travel(3)->hours();
    clientWrites($this, 'vous livrez à Kipé ?');
    expect(aiReplies($this))->toHaveCount(1);
});

test('si l\'IA ne répond pas : un seul message de secours (mode automatique)', function () {
    $this->model->script = fn () => throw new AiUnavailable('indisponible');
    clientWrites($this, 'bonjour');
    expect(aiReplies($this)->all())->toBe([ShopioAssistant::FALLBACK]);
});

test('IA désactivée, STOP, et trop de messages par minute : pas de réponse', function () {
    clientWrites($this, 'STOP');
    expect(aiReplies($this))->toHaveCount(0);

    foreach (range(1, 8) as $i) {
        clientWrites($this, "message {$i}");
    }
    expect(aiReplies($this))->toHaveCount(6);

    $this->settings->update(['enabled' => false]);
    $this->travel(2)->minutes();
    clientWrites($this, 'encore');
    expect(aiReplies($this))->toHaveCount(6);
});

/* ── Négociation ── */

test('creer_offre : proposition transmise au vendeur, jamais acceptée par l\'IA sans prix minimum fixé', function () {
    $r = app(ShopioTools::class)->execute('creer_offre', ['produit_id' => $this->product->id, 'prix' => 140000], ctx($this));
    expect($r['resultat'])->toBe('proposition_transmise')
        ->and(ShopMessage::where('type', ShopMessage::TYPE_PRICE_PROPOSAL)->sole()->only(['sender_id', 'proposal_status']))
        ->toBe(['sender_id' => $this->client->id, 'proposal_status' => 'pending'])
        ->and(ShopMessage::where('type', ShopMessage::TYPE_PRICE_OFFER)->count())->toBe(0);

    expect(app(ShopioTools::class)->execute('creer_offre', ['produit_id' => $this->product->id, 'prix' => 160000], ctx($this)))->toHaveKey('erreur')
        ->and(app(ShopioTools::class)->execute('creer_offre', ['produit_id' => $this->product->id, 'prix' => 140000], new AiContext($this->shop, null, $this->settings)))->toHaveKey('erreur');
});

test('creer_offre : dans la limite de remise fixée par le vendeur, une offre à confirmer est envoyée', function () {
    $this->settings->update(['max_discount_percent' => 10]);

    $ok = app(ShopioTools::class)->execute('creer_offre', ['produit_id' => $this->product->id, 'prix' => 135000], ctx($this));
    $tooLow = app(ShopioTools::class)->execute('creer_offre', ['produit_id' => $this->product->id, 'prix' => 120000], ctx($this));

    expect($ok['resultat'])->toBe('offre_envoyee')->and($tooLow['resultat'])->toBe('proposition_transmise');
    $offer = ShopMessage::where('type', ShopMessage::TYPE_PRICE_OFFER)->sole();
    expect($offer->only(['sender_id', 'receiver_id', 'is_ai']))->toBe(['sender_id' => $this->vendor->id, 'receiver_id' => $this->client->id, 'is_ai' => true])
        ->and((int) $offer->proposed_price)->toBe(135000);
});
