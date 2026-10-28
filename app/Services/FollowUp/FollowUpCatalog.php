<?php

namespace App\Services\FollowUp;

/**
 * Les situations de relance, leurs réglages de départ et leurs modèles de messages par défaut.
 * Dans un modèle, un passage entre [[ ]] disparaît si une de ses variables est vide
 * (ex. la phrase du code promo quand aucun code n'est choisi).
 */
class FollowUpCatalog
{
    public const OFFER        = 'offre_sans_reponse';
    public const COUNTER      = 'contre_offre_sans_reponse';
    public const ABANDONED    = 'conversation_abandonnee';
    public const ORDER        = 'commande_a_confirmer';
    public const LOYALTY      = 'fidelite';
    public const RESTOCK      = 'retour_en_stock';
    public const INACTIVE_VENDOR = 'vendeur_inactif';

    public const TONES = ['amical' => 'Amical', 'professionnel' => 'Professionnel', 'urgent' => 'Urgent'];
    public const LOCALES = ['fr' => 'Français', 'en' => 'Anglais'];
    public const CHANNELS = [
        'in_app'    => 'Notification dans l\'appli',
        'messaging' => 'Message dans la messagerie Shopio',
        'sms'       => 'SMS',
        'whatsapp'  => 'WhatsApp',
    ];
    public const VARIABLES = [
        '{prenom_client}' => 'Prénom du client',
        '{nom_boutique}'  => 'Nom de la boutique',
        '{produit}'       => 'Nom du produit',
        '{prix}'          => 'Prix (ou montant de la commande)',
        '{prix_negocie}'  => 'Prix de l\'offre',
        '{lien_produit}'  => 'Lien du produit',
        '{lien_commande}' => 'Lien de la commande',
        '{lien_boutique}' => 'Lien de la boutique',
        '{code_promo}'    => 'Code promo choisi',
        '{date_limite}'   => 'Fin de validité du code promo',
    ];

    /** Situation → réglages de départ (délais en minutes) */
    public static function types(): array
    {
        $h = 60; $d = 1440;
        return [
            self::OFFER => [
                'label' => 'Offre de prix sans réponse', 'audience' => 'client', 'subject' => 'offer',
                'help'  => 'Tu as envoyé une offre de prix et le client ne l\'a ni confirmée ni refusée.',
                'enabled' => true, 'delay' => 2 * $h, 'max' => 2, 'interval' => 1 * $d, 'channels' => ['messaging', 'in_app'],
            ],
            self::COUNTER => [
                'label' => 'Contre-offre sans réponse', 'audience' => 'client', 'subject' => 'offer',
                'help'  => 'Tu as fait une contre-offre au client et il n\'a pas répondu.',
                'enabled' => true, 'delay' => 2 * $h, 'max' => 2, 'interval' => 1 * $d, 'channels' => ['messaging', 'in_app'],
            ],
            self::ABANDONED => [
                'label' => 'Conversation abandonnée', 'audience' => 'client', 'subject' => 'offer',
                'help'  => 'Le client a posé une question sur un produit, tu as répondu, puis plus rien et pas de commande.',
                'enabled' => false, 'delay' => 1 * $d, 'max' => 1, 'interval' => 2 * $d, 'channels' => ['messaging'],
            ],
            self::ORDER => [
                'label' => 'Commande à confirmer', 'audience' => 'vendor', 'subject' => 'order',
                'help'  => 'Rappel pour toi : une commande attend encore ta confirmation.',
                'enabled' => true, 'delay' => 1 * $h, 'max' => 2, 'interval' => 3 * $h, 'channels' => ['in_app'],
            ],
            self::LOYALTY => [
                'label' => 'Client qui ne commande plus', 'audience' => 'client', 'subject' => 'client',
                'help'  => 'Le client a déjà commandé chez toi mais plus depuis un moment.',
                'enabled' => false, 'delay' => 30 * $d, 'max' => 1, 'interval' => 14 * $d, 'channels' => ['in_app', 'messaging'],
            ],
            self::RESTOCK => [
                'label' => 'Produit de nouveau en stock', 'audience' => 'client', 'subject' => 'product',
                'help'  => 'Le client avait demandé à être prévenu ; le produit vient de revenir en stock.',
                'enabled' => true, 'delay' => 0, 'max' => 2, 'interval' => 2 * $d, 'channels' => ['in_app'],
            ],
            self::INACTIVE_VENDOR => [
                'label' => 'Vendeur inactif', 'audience' => 'vendor', 'subject' => 'shop', 'admin' => true,
                'help'  => 'Boutique sans produit en ligne, ou vendeur qui ne s\'est pas connecté depuis le délai.',
                'enabled' => true, 'delay' => 14 * $d, 'max' => 3, 'interval' => 7 * $d, 'channels' => ['in_app'],
            ],
        ];
    }

    public static function get(string $type): array
    {
        return self::types()[$type] ?? throw new \InvalidArgumentException("Relance inconnue : {$type}");
    }

    /** Situations que le vendeur règle lui-même (pas « vendeur inactif », réglée par l'admin) */
    public static function vendorTypes(): array
    {
        return array_filter(self::types(), fn ($t) => empty($t['admin']));
    }

    public static function isClientFacing(string $type): bool
    {
        return self::get($type)['audience'] === 'client';
    }

    /** Modèle par défaut d'une situation, dans un ton et une langue */
    public static function template(string $type, string $tone = 'amical', string $locale = 'fr'): string
    {
        if ($locale === 'en') {
            return self::ENGLISH[$type] ?? self::FRENCH[$type]['amical'];
        }
        return self::FRENCH[$type][$tone] ?? self::FRENCH[$type]['amical'];
    }

    private const FRENCH = [
        self::OFFER => [
            'amical'        => 'Bonjour {prenom_client}, {nom_boutique} t\'a proposé {produit} à {prix_negocie}. L\'offre t\'attend toujours : tu peux la confirmer dans la messagerie.[[ Code promo : {code_promo}.]] {lien_produit}',
            'professionnel' => 'Bonjour {prenom_client}, nous vous rappelons notre offre pour {produit} au prix de {prix_negocie}. Vous pouvez la confirmer depuis la messagerie Shopio.[[ Code promo : {code_promo}.]] {lien_produit}',
            'urgent'        => '{prenom_client}, l\'offre de {nom_boutique} pour {produit} à {prix_negocie} t\'attend encore. Confirme-la vite dans la messagerie pour la garder ! {lien_produit}',
        ],
        self::COUNTER => [
            'amical'        => 'Bonjour {prenom_client}, {nom_boutique} t\'a fait une contre-offre pour {produit} : {prix_negocie}. Tu peux l\'accepter ou proposer un autre prix dans la messagerie. {lien_produit}',
            'professionnel' => 'Bonjour {prenom_client}, nous avons répondu à votre proposition pour {produit} avec un prix de {prix_negocie}. Vous pouvez l\'accepter depuis la messagerie Shopio. {lien_produit}',
            'urgent'        => '{prenom_client}, notre contre-offre pour {produit} à {prix_negocie} t\'attend. Réponds vite pour la garder ! {lien_produit}',
        ],
        self::ABANDONED => [
            'amical'        => 'Bonjour {prenom_client}, tu t\'intéressais à {produit} ({prix}) chez {nom_boutique}. Tu as encore une question ? On est là pour t\'aider.[[ Le code {code_promo} t\'attend pour ta commande.]] {lien_produit}',
            'professionnel' => 'Bonjour {prenom_client}, suite à votre message sur {produit} ({prix}), avez-vous besoin d\'autres informations ? Nous restons à votre disposition. {lien_produit}',
            'urgent'        => '{prenom_client}, {produit} est toujours disponible à {prix} chez {nom_boutique}. Commande-le avant qu\'il ne parte ![[ Code {code_promo}[[ valable jusqu\'au {date_limite}]].]] {lien_produit}',
        ],
        self::ORDER => [
            'amical'        => 'La commande de {prenom_client} ({prix}) attend ta confirmation. Confirme-la vite pour ne pas perdre la vente : {lien_commande}',
            'professionnel' => 'Rappel : la commande de {prenom_client} d\'un montant de {prix} est toujours en attente de confirmation. {lien_commande}',
            'urgent'        => 'Urgent : {prenom_client} attend que tu confirmes sa commande de {prix}. Confirme-la maintenant : {lien_commande}',
        ],
        self::LOYALTY => [
            'amical'        => 'Bonjour {prenom_client}, ça fait un moment ! {nom_boutique} a des nouveautés pour toi.[[ Utilise le code {code_promo} pour ta prochaine commande[[ jusqu\'au {date_limite}]].]] {lien_boutique}',
            'professionnel' => 'Bonjour {prenom_client}, {nom_boutique} vous remercie pour votre fidélité et vous invite à découvrir ses produits.[[ Code promo : {code_promo}[[, valable jusqu\'au {date_limite}]].]] {lien_boutique}',
            'urgent'        => '{prenom_client}, {nom_boutique} pense à toi ![[ Ton code {code_promo} t\'attend[[ jusqu\'au {date_limite}]] : ne le laisse pas passer.]] {lien_boutique}',
        ],
        self::RESTOCK => [
            'amical'        => 'Bonne nouvelle {prenom_client} : {produit} est de nouveau en stock chez {nom_boutique}, à {prix}. {lien_produit}',
            'professionnel' => 'Bonjour {prenom_client}, le produit {produit} que vous attendiez est de nouveau disponible au prix de {prix}. {lien_produit}',
            'urgent'        => '{prenom_client}, {produit} est de retour en stock à {prix}. Commande-le avant qu\'il ne reparte ! {lien_produit}',
        ],
        self::INACTIVE_VENDOR => [
            'amical'        => 'Bonjour {prenom_client}, ta boutique {nom_boutique} t\'attend sur Shopio ! Ajoute tes produits et partage ton lien pour recevoir tes premières commandes. {lien_boutique}',
            'professionnel' => 'Bonjour {prenom_client}, votre boutique {nom_boutique} est peu active sur Shopio. Publiez vos produits pour être visible auprès des acheteurs. {lien_boutique}',
            'urgent'        => '{prenom_client}, des clients cherchent des produits sur Shopio en ce moment. Mets vite tes produits en ligne dans {nom_boutique} ! {lien_boutique}',
        ],
    ];

    private const ENGLISH = [
        self::OFFER           => 'Hello {prenom_client}, {nom_boutique} offered you {produit} at {prix_negocie}. The offer is still waiting for you in your messages. {lien_produit}',
        self::COUNTER         => 'Hello {prenom_client}, {nom_boutique} made a counter-offer for {produit}: {prix_negocie}. You can accept it in your messages. {lien_produit}',
        self::ABANDONED       => 'Hello {prenom_client}, you asked about {produit} ({prix}) at {nom_boutique}. Any other question? We are here to help. {lien_produit}',
        self::ORDER           => 'The order from {prenom_client} ({prix}) is waiting for your confirmation: {lien_commande}',
        self::LOYALTY         => 'Hello {prenom_client}, it has been a while! {nom_boutique} has new products for you.[[ Use code {code_promo} on your next order.]] {lien_boutique}',
        self::RESTOCK         => 'Good news {prenom_client}: {produit} is back in stock at {nom_boutique}, for {prix}. {lien_produit}',
        self::INACTIVE_VENDOR => 'Hello {prenom_client}, your shop {nom_boutique} is waiting for you on Shopio! Add your products to get your first orders. {lien_boutique}',
    ];

    /** Phrase de désinscription ajoutée aux relances envoyées aux clients */
    public static function stopFooter(string $locale = 'fr'): string
    {
        return $locale === 'en' ? 'Reply STOP to stop these reminders.' : 'Réponds STOP pour ne plus recevoir ces rappels.';
    }
}
