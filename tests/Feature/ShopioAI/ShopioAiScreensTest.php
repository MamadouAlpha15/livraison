<?php

use App\Models\AiConversationState;
use App\Models\AiReplySuggestion;
use App\Models\AiUsage;
use App\Models\FaqEntry;
use App\Models\ShopAiSetting;
use App\Models\ShopioAiSetting;
use App\Models\ShopMessage;
use App\Models\User;
use App\Services\PushService;
use App\Services\ShopioAI\ChatModel;
use App\Services\ShopioAI\ChatResult;
use Tests\Support\CampaignFixtures as F;

beforeEach(function () {
    app()->instance(ChatModel::class, new class implements ChatModel {
        public function run(string $system, array $messages, array $tools, callable $executeTool): ChatResult { return new ChatResult('Bonjour !', 10, 5); }
    });
    app()->instance(PushService::class, new class { public function sendToUser(...$a): void {} });
    $this->shop = F::shop();
    $this->shop->update(['plan' => 'pro', 'plan_expires_at' => now()->addMonth()]);
    $this->vendor = $this->shop->owner;
    $this->vendor->forceFill(['shop_id' => $this->shop->id])->save();
    $this->client = User::factory()->create(['role' => 'client', 'name' => 'Mariama']);
});

function settingsForm(array $over = []): array
{
    return $over + ['enabled' => '1', 'mode' => 'auto', 'address_form' => 'vous', 'max_discount_percent' => 10,
        'delivery_info' => 'Conakry : 20 000 GNF', 'return_policy' => 'Échange sous 3 jours'];
}

test('le vendeur règle Shopio IA ; la réponse automatique est réservée au plan Pro', function () {
    $this->actingAs($this->vendor)->get(route('boutique.shopio-ai.index'))->assertOk()->assertSee('Shopio IA')->assertSee('0 / 1000');
    $this->actingAs($this->vendor)->put(route('boutique.shopio-ai.update'), settingsForm())->assertSessionHasNoErrors();
    expect(ShopAiSetting::where('shop_id', $this->shop->id)->sole()->only(['enabled', 'mode', 'address_form', 'max_discount_percent']))
        ->toBe(['enabled' => true, 'mode' => 'auto', 'address_form' => 'vous', 'max_discount_percent' => 10]);

    $this->shop->update(['plan' => 'free', 'plan_expires_at' => null]);
    $this->actingAs($this->vendor)->put(route('boutique.shopio-ai.update'), settingsForm())->assertSessionHasErrors('mode');
});

test('suggestion envoyée en un clic (modifiable), marquée Shopio IA ; ou ignorée', function () {
    ShopAiSetting::create(['shop_id' => $this->shop->id, 'enabled' => true, 'mode' => 'suggestion']);
    ShopMessage::create(['shop_id' => $this->shop->id, 'sender_id' => $this->client->id, 'receiver_id' => $this->vendor->id, 'type' => 'text', 'body' => 'bonjour']);
    $s = AiReplySuggestion::sole();

    $this->actingAs($this->vendor)->get(route('boutique.shopio-ai.index'))->assertSee('Réponses à envoyer')->assertSee('Bonjour !');
    $this->actingAs($this->vendor)->post(route('boutique.shopio-ai.suggestions.send', $s), ['body' => 'Bonjour Mariama, je regarde ça.'])->assertSessionHas('success');

    $sent = ShopMessage::where('is_ai', true)->sole();
    expect($sent->body)->toBe('Bonjour Mariama, je regarde ça.')->and($sent->receiver_id)->toBe($this->client->id)
        ->and($s->fresh()->status)->toBe('sent');

    // Déjà envoyée : pas de doublon
    $this->actingAs($this->vendor)->post(route('boutique.shopio-ai.suggestions.send', $s))->assertSessionHasErrors('suggestion');
    expect(ShopMessage::where('is_ai', true)->count())->toBe(1);

    // Un autre vendeur ne peut pas y toucher
    $other = F::shop();
    $other->owner->forceFill(['shop_id' => $other->id])->save();
    $this->actingAs($other->owner)->post(route('boutique.shopio-ai.suggestions.dismiss', $s))->assertNotFound();
});

test('« Reprendre la main » arrête l\'IA pour ce client ; « Rendre la main » la relance', function () {
    ShopAiSetting::create(['shop_id' => $this->shop->id, 'enabled' => true, 'mode' => 'auto']);
    ShopMessage::create(['shop_id' => $this->shop->id, 'sender_id' => $this->client->id, 'receiver_id' => $this->vendor->id, 'type' => 'text', 'body' => 'salut']);
    $this->actingAs($this->vendor)->get(route('boutique.shopio-ai.index'))->assertSee('Mariama')->assertSee('Reprendre la main');

    $this->actingAs($this->vendor)->post(route('boutique.shopio-ai.take-over', $this->client));
    ShopMessage::create(['shop_id' => $this->shop->id, 'sender_id' => $this->client->id, 'receiver_id' => $this->vendor->id, 'type' => 'text', 'body' => 'allo']);
    expect(ShopMessage::where('is_ai', true)->count())->toBe(1);

    $this->actingAs($this->vendor)->post(route('boutique.shopio-ai.hand-back', $this->client));
    expect(AiConversationState::sole()->pausesAi())->toBeFalse();
    ShopMessage::create(['shop_id' => $this->shop->id, 'sender_id' => $this->client->id, 'receiver_id' => $this->vendor->id, 'type' => 'text', 'body' => 'toujours là ?']);
    expect(ShopMessage::where('is_ai', true)->count())->toBe(2);
});

test('la conversation du vendeur indique les messages de Shopio IA', function () {
    ShopMessage::create(['shop_id' => $this->shop->id, 'sender_id' => $this->vendor->id, 'receiver_id' => $this->client->id, 'type' => 'text', 'body' => 'Réponse IA', 'is_ai' => true]);
    $this->actingAs($this->vendor)->getJson(route('boutique.messages.conversation', ['client_id' => $this->client->id]))
        ->assertOk()->assertJsonPath('messages.0.is_ai', true);
});

test('admin : coût par boutique, limites par plan, FAQ modifiable ; interdit aux vendeurs', function () {
    $staff = User::factory()->create(['role' => 'staff']);
    AiUsage::create(['shop_id' => $this->shop->id, 'month' => now()->format('Y-m'), 'messages' => 12, 'input_tokens' => 250000, 'output_tokens' => 10000, 'cost_micro_usd' => 1_200_000]);

    $this->actingAs($this->vendor)->get(route('admin.shopio-ai.index'))->assertForbidden();
    $this->actingAs($staff)->get(route('admin.shopio-ai.index'))->assertOk()
        ->assertSee($this->shop->name)->assertSee("1,20\u{00A0}$")->assertSee('Comment payer ma commande ?');

    $this->actingAs($staff)->put(route('admin.shopio-ai.settings'), ['enabled' => '1', 'free_monthly_limit' => 20, 'pro_monthly_limit' => 2000, 'free_auto_allowed' => '1']);
    expect(ShopioAiSetting::current()->only(['free_monthly_limit', 'pro_monthly_limit', 'free_auto_allowed']))
        ->toBe(['free_monthly_limit' => 20, 'pro_monthly_limit' => 2000, 'free_auto_allowed' => true]);

    $this->actingAs($staff)->post(route('admin.shopio-ai.faq.store'), ['category' => 'retours', 'question' => 'Puis-je retourner un article ?', 'answer' => 'Chaque boutique fixe sa politique de retour.']);
    $entry = FaqEntry::where('category', 'retours')->sole();
    expect($entry->is_active)->toBeTrue();
    $this->actingAs($staff)->put(route('admin.shopio-ai.faq.update', $entry), ['category' => 'retours', 'question' => $entry->question, 'answer' => $entry->answer]);
    expect($entry->fresh()->is_active)->toBeFalse();
    $this->actingAs($staff)->delete(route('admin.shopio-ai.faq.destroy', $entry));
    expect(FaqEntry::where('category', 'retours')->exists())->toBeFalse();
});
