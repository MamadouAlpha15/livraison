<?php

use App\Models\Order;
use App\Models\SupportMessage;
use App\Models\SupportTicket;
use App\Models\User;
use App\Support\Ai\TextModel;
use Tests\Support\CampaignFixtures as F;

beforeEach(function () {
    // IA simulée : garde ce qu'on lui envoie et renvoie une réponse fixe
    $this->ai = new class implements TextModel {
        public array $calls = [];
        public string $reply = '« Bonjour Mariama, votre commande n° 1 est en cours de livraison. »';
        public function complete(string $system, array $messages, int $maxTokens = 1500): string
        {
            $this->calls[] = compact('system', 'messages');
            return $this->reply;
        }
    };
    app()->instance(TextModel::class, $this->ai);

    $this->admin = User::factory()->create(['role' => 'superadmin']);
    $this->client = User::factory()->create(['role' => 'client', 'name' => 'Mariama Diallo']);
    $this->ticket = SupportTicket::create(['user_id' => $this->client->id, 'subject' => 'Ma commande n\'arrive pas', 'status' => 'open']);
    SupportMessage::create(['ticket_id' => $this->ticket->id, 'user_id' => $this->client->id, 'body' => 'Bonjour, j\'attends ma commande depuis 3 jours.']);
});

test('l\'IA propose une réponse à partir de la conversation et des vraies commandes du client, sans rien envoyer', function () {
    $shop = F::shop();
    $order = Order::forceCreate(['user_id' => $this->client->id, 'shop_id' => $shop->id, 'total' => 175000, 'status' => Order::STATUS_EN_LIVRAISON]);

    $this->actingAs($this->admin)
        ->postJson(route('admin.support.ai', $this->ticket), ['mode' => 'rediger'])
        ->assertOk()
        ->assertJson(['reply' => 'Bonjour Mariama, votre commande n° 1 est en cours de livraison.']); // sans les guillemets

    $prompt = $this->ai->calls[0]['messages'][0]['content'];
    expect($prompt)->toContain('Ma commande n\'arrive pas')
        ->toContain('Client : Bonjour, j\'attends ma commande depuis 3 jours.')
        ->toContain('Commande n° ' . $order->id)
        ->toContain('175 000 GNF')
        ->toContain('en_livraison')
        ->toContain('Rédige la prochaine réponse')
        ->and($this->ai->calls[0]['system'])->toContain('N\'invente jamais')
        ->toContain('150 000 GNF');

    // Rien n'est envoyé au client : l'admin relit et envoie lui-même
    expect(SupportMessage::count())->toBe(1);
});

test('« Améliorer mon texte » transmet le brouillon et la consigne ; impossible sans brouillon', function () {
    $this->actingAs($this->admin)
        ->postJson(route('admin.support.ai', $this->ticket), ['mode' => 'ameliorer', 'draft' => 'on regarde ca', 'instruction' => 'plus rassurant'])
        ->assertOk();

    $prompt = $this->ai->calls[0]['messages'][0]['content'];
    expect($prompt)->toContain("<brouillon>\non regarde ca\n</brouillon>")->toContain('Consigne de l\'admin pour cette réponse : plus rassurant');

    $this->actingAs($this->admin)
        ->postJson(route('admin.support.ai', $this->ticket), ['mode' => 'ameliorer', 'draft' => ''])
        ->assertStatus(503)->assertJson(['error' => 'Écris d\'abord ta réponse : l\'IA l\'améliorera ensuite.']);
});

test('sans clé ANTHROPIC_API_KEY, l\'admin voit pourquoi l\'IA ne répond pas', function () {
    app()->forgetInstance(TextModel::class);
    app()->offsetUnset(TextModel::class);
    config(['services.anthropic.key' => '']);
    (new App\Providers\AppServiceProvider(app()))->register();

    $this->actingAs($this->admin)
        ->postJson(route('admin.support.ai', $this->ticket), ['mode' => 'rediger'])
        ->assertStatus(503)
        ->assertJsonPath('error', fn ($e) => str_contains($e, 'ANTHROPIC_API_KEY'));
});

test('réservé à l\'équipe : un client ou une boutique ne peut pas utiliser l\'IA du support', function () {
    $vendor = User::factory()->create(['role' => 'admin']);

    $this->actingAs($this->client)->postJson(route('admin.support.ai', $this->ticket), ['mode' => 'rediger'])->assertForbidden();
    $this->actingAs($vendor)->postJson(route('admin.support.ai', $this->ticket), ['mode' => 'rediger'])->assertForbidden();
    expect($this->ai->calls)->toBeEmpty();
});

test('les pages support de l\'admin affichent les boutons de l\'IA', function () {
    $this->actingAs($this->admin)->get(route('admin.support.index'))->assertOk()
        ->assertSee('Proposer une réponse')->assertSee('Améliorer mon texte');
    $this->actingAs($this->admin)->get(route('support.show', $this->ticket))->assertOk()
        ->assertSee('Proposer une réponse');
});
