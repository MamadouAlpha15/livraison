<?php

namespace App\Support;

use App\Models\Order;
use Illuminate\Database\Eloquent\Builder;

/**
 * Page « Commandes » façon Sellio : 8 étapes de suivi, bâties sur les 5 statuts de Shopio
 * (pour que livreurs, entreprises de livraison, suivi client et statistiques restent inchangés) :
 *
 *   Non traitée  = en_attente (sans précision)     Confirmée    = confirmée
 *   Injoignable  = en_attente + followup injoignable  En livraison = en_livraison
 *   Programmée   = en_attente + followup programmee   Livrée       = livrée
 *   Rejetée      = annulée (sans précision)          Retournée    = annulée + followup retournee
 *
 * Réglages (shops.storefront) : 'orders' {wa} message WhatsApp de confirmation ;
 * 'carts' {enabled, delay, closers, wa} paniers abandonnés.
 */
class OrderBoard
{
    /** [libellé, statut Shopio, précision, couleur]. */
    public const STAGES = [
        'nouvelle'     => ['Non traitée', Order::STATUS_EN_ATTENTE, null, 'amber'],
        'confirmee'    => ['Confirmée', Order::STATUS_CONFIRMEE, null, 'blue'],
        'livree'       => ['Livrée', Order::STATUS_LIVREE, null, 'green'],
        'rejetee'      => ['Rejetée', Order::STATUS_ANNULEE, null, 'red'],
        'injoignable'  => ['Injoignable', Order::STATUS_EN_ATTENTE, 'injoignable', 'grey'],
        'programmee'   => ['Programmée', Order::STATUS_EN_ATTENTE, 'programmee', 'violet'],
        'en_livraison' => ['En livraison', Order::STATUS_EN_LIVRAISON, null, 'sky'],
        'retournee'    => ['Retournée', Order::STATUS_ANNULEE, 'retournee', 'brown'],
    ];

    /** Ordre des boutons « Actions » de la fiche commande. */
    public const ACTIONS = ['confirmee', 'programmee', 'en_livraison', 'livree', 'rejetee', 'injoignable'];

    public const DELAYS = [1 => '1 minute', 2 => '2 minutes', 5 => '5 minutes', 10 => '10 minutes', 30 => '30 minutes', 60 => '1 heure'];

    public const WA_CONFIRM = "Bonjour {prenom} 👋\n\nMerci pour votre commande n° {numero} chez {boutique} : {produits}.\nTotal : {total}, à payer à la livraison.\n\nPouvez-vous me confirmer votre adresse de livraison ?";
    public const WA_CART = "Bonjour {prenom} !\n\nVous avez commencé une commande sur {boutique} : {produits}.\n\nSouhaitez-vous qu'on la finalise ensemble ? Livraison et paiement à la réception.";

    public static function stageOf(Order $o): string
    {
        $status = (string) $o->getRawOriginal('status') ?: (string) $o->status;
        $f = $o->followup;
        return match (true) {
            in_array($status, ['en_attente', 'pending', 'en attente'], true) => $f === 'injoignable' ? 'injoignable' : ($f === 'programmee' ? 'programmee' : 'nouvelle'),
            in_array($status, ['confirmée', 'confirmed'], true) => 'confirmee',
            in_array($status, ['en_livraison', 'delivering'], true) => 'en_livraison',
            in_array($status, ['livrée', 'delivered'], true) => 'livree',
            default => $f === 'retournee' ? 'retournee' : 'rejetee',
        };
    }

    /** Filtre une requête de commandes sur une étape. */
    public static function scope(Builder $q, string $stage): Builder
    {
        [, $status, $follow] = self::STAGES[$stage];
        $q->where('status', $status);
        if ($stage === 'nouvelle' || $stage === 'rejetee') $q->where(fn ($w) => $w->whereNull('followup')->orWhereNotIn('followup', ['injoignable', 'programmee', 'retournee']));
        elseif ($follow) $q->where('followup', $follow);
        return $q;
    }

    // ─────────────────────────────────────────────────────────────── Réglages

    public static function settings(array $sf): array
    {
        $o = is_array($sf['orders'] ?? null) ? $sf['orders'] : [];
        $c = is_array($sf['carts'] ?? null) ? $sf['carts'] : [];
        return [
            'orders' => ['wa' => self::text($o['wa'] ?? '', 600) ?: self::WA_CONFIRM],
            'carts'  => [
                'enabled' => array_key_exists('enabled', $c) ? filter_var($c['enabled'], FILTER_VALIDATE_BOOLEAN) : true,
                'delay'   => isset(self::DELAYS[(int) ($c['delay'] ?? 0)]) ? (int) $c['delay'] : 2,
                'closers' => array_key_exists('closers', $c) ? filter_var($c['closers'], FILTER_VALIDATE_BOOLEAN) : true,
                'wa'      => self::text($c['wa'] ?? '', 600) ?: self::WA_CART,
            ],
        ];
    }

    private static function text($v, int $max): string
    {
        $v = strip_tags(mb_scrub((string) (is_scalar($v) ? $v : ''), 'UTF-8'));
        return mb_substr(trim(str_replace("\r", '', $v)), 0, $max);
    }

    /** Remplit un modèle de message ({prenom}, {boutique}, {produits}, {total}, {numero}). */
    public static function fill(string $tpl, array $vars): string
    {
        return strtr($tpl, array_combine(array_map(fn ($k) => '{' . $k . '}', array_keys($vars)), array_values($vars)));
    }

    /** Numéro au format international guinéen pour wa.me / tel: (224 + 9 chiffres). */
    public static function intl(?string $phone): ?string
    {
        $d = preg_replace('/\D/', '', (string) $phone);
        if ($d === '') return null;
        if (strlen($d) === 9) $d = '224' . $d;
        return $d;
    }
}
