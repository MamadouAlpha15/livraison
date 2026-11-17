<?php

namespace App\Services\ShopioAI;

use App\Models\Product;
use App\Models\Shop;
use App\Services\FollowUp\FollowUpCatalog as Cat;
use App\Support\Ai\AiUnavailable;
use App\Support\Ai\TextModel;

/**
 * « Générer avec Shopio IA » dans le réglage d'une relance : propose un modèle de message adapté
 * à la boutique, à ses produits, à la situation et au ton. Le vendeur le relit et le modifie avant d'enregistrer.
 */
class FollowUpWriter
{
    public function __construct(private TextModel $model) {}

    /** @throws AiUnavailable */
    public function write(Shop $shop, string $type, string $tone, string $locale, ?string $wish = null): string
    {
        $situation = Cat::get($type);
        $products = Product::where('shop_id', $shop->id)->where('is_active', true)->latest()->limit(5)->pluck('name')->implode(', ');
        $variables = collect(Cat::VARIABLES)->map(fn ($label, $var) => "{$var} : {$label}")->implode("\n");

        $system = <<<TXT
Tu écris des messages de relance courts pour des boutiques en ligne sur Shopio, une marketplace en Guinée (paiement à la livraison, montants en GNF).
Règles :
- Message de 1 à 3 phrases, 320 caractères au plus, simple et poli, lisible sur un téléphone.
- Utilise les variables entre accolades telles quelles (elles sont remplacées à l'envoi) ; n'en invente aucune autre.
- N'invente aucun prix, stock, délai ni promotion : passe par les variables.
- Tu peux entourer une phrase de [[ ]] : elle disparaît si une de ses variables est vide (utile pour {code_promo} et {date_limite}).
- Pas de phrase de désinscription : elle est ajoutée automatiquement.
- Écris uniquement le message.
TXT;

        $prompt = "Boutique : {$shop->name}\n"
            . ($products ? "Quelques produits : {$products}\n" : '')
            . "Situation : {$situation['label']} — {$situation['help']}\n"
            . 'Destinataire : ' . ($situation['audience'] === 'client' ? 'le client' : 'le vendeur') . "\n"
            . 'Ton : ' . (Cat::TONES[$tone] ?? $tone) . ($tone === 'professionnel' ? ' (vouvoiement)' : ' (tutoiement)') . "\n"
            . 'Langue : ' . (Cat::LOCALES[$locale] ?? 'Français') . "\n"
            . "Variables possibles :\n{$variables}\n"
            . 'Exemple de message actuel : ' . Cat::template($type, $tone, $locale)
            . (filled($wish) ? "\nSouhait du vendeur : " . mb_substr(trim($wish), 0, 200) : '');

        $text = trim($this->model->complete($system, [['role' => 'user', 'content' => $prompt]], 600), " \n\"«»");
        return mb_substr($text, 0, 1000);
    }
}
