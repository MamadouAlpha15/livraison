<?php

namespace App\Support;

/**
 * Académie Shopio : guides courts et pratiques pour les vendeurs (/academie).
 * Conseils de méthode uniquement : aucun chiffre de résultat promis.
 * Chaque guide : [titre, résumé, minutes de lecture, icône, étapes [titre, texte]], et un lien vers Shopio.
 */
class Academy
{
    public const GUIDES = [
        'trouver-un-produit' => [
            'title'   => 'Trouver un produit qui se vend',
            'summary' => 'Les questions à te poser avant d’acheter du stock, pour éviter les produits qui dorment.',
            'minutes' => 4,
            'icon'    => 'spark',
            'steps'   => [
                ['Pars d’un problème, pas d’un produit', 'Un produit se vend bien quand il règle un souci concret : gagner du temps, être plus beau, protéger un objet cher. Écris en une phrase le problème que ton produit règle. Si tu n’y arrives pas, cherche-en un autre.'],
                ['Vérifie que tu peux gagner de l’argent', 'Additionne le prix d’achat, le transport, la livraison et un peu de pub. Le prix de vente doit rester attirant pour le client tout en te laissant une vraie marge. La page Bénéfices de Shopio fait ce calcul pour toi une fois les premières ventes livrées.'],
                ['Choisis un produit facile à livrer', 'Léger, solide, qui ne casse pas et ne périme pas vite. Un produit fragile ou encombrant coûte cher en livraison et en retours.'],
                ['Regarde ce que les gens demandent déjà', 'Observe les commentaires sous les publicités, les questions dans les groupes WhatsApp et Facebook, ce que tes proches achètent. Les mêmes demandes qui reviennent sont de bons signes.'],
                ['Teste avec un petit stock', 'Commence avec quelques pièces, publie une page produit et une petite publicité. Si les commandes arrivent et sont livrées, recommande plus. Sinon, tu n’as pas perdu grand-chose.'],
            ],
            'cta'     => ['Créer ta page produit avec Shopio IA', 'register'],
        ],
        'photos-au-telephone' => [
            'title'   => 'Prendre de belles photos avec ton téléphone',
            'summary' => 'Pas besoin de studio : la lumière du jour, un fond simple et les bons angles suffisent.',
            'minutes' => 3,
            'icon'    => 'phone',
            'steps'   => [
                ['Utilise la lumière du jour', 'Place-toi près d’une fenêtre, sans soleil direct. Évite le flash : il écrase les couleurs et fait des reflets.'],
                ['Choisis un fond simple', 'Un mur clair, un drap blanc ou une table en bois. Le client doit voir le produit, pas le désordre autour.'],
                ['Montre le produit sous tous ses angles', 'Face, dos, côté, un détail (couture, matière, étiquette), et le produit en taille réelle à côté d’un objet connu ou porté par quelqu’un.'],
                ['Ajoute une photo « dans la vie »', 'Le sac porté à l’épaule, la crème sur la main, la montre au poignet. Le client s’imagine mieux avec.'],
                ['Reste honnête', 'Pas de filtre qui change la couleur. Un client qui reçoit un produit différent de la photo refuse le colis, et c’est un retour qui te coûte.'],
            ],
            'cta'     => ['Ajouter tes photos sur Shopio', 'register'],
        ],
        'page-produit-qui-vend' => [
            'title'   => 'Écrire une page produit qui vend',
            'summary' => 'Le titre, les avantages, les packs et les réponses aux questions que tes clients se posent.',
            'minutes' => 4,
            'icon'    => 'layers',
            'steps'   => [
                ['Un titre clair', 'Ce que c’est, pour qui, et ce qui le rend spécial. Exemple : « Sac en cuir pour femme, grand format, fermeture sécurisée ».'],
                ['Des avantages, pas seulement des caractéristiques', '« Cuir épais » devient « Il garde sa forme même rempli ». Trois à cinq avantages courts suffisent.'],
                ['Propose des packs', 'Deux ou trois produits à prix réduit augmentent le montant de chaque commande. Sur Shopio, les packs s’affichent et s’appliquent tout seuls à la commande.'],
                ['Réponds aux questions avant qu’on te les pose', 'Délai et frais de livraison, paiement à la livraison, tailles, entretien, échange. Une petite FAQ évite des appels et rassure.'],
                ['Montre de vrais avis', 'Seulement ceux de vrais clients. Un faux avis découvert fait perdre la confiance de tout le monde.'],
            ],
            'cta'     => ['Construire ta page produit en sections', 'register'],
        ],
        'publicite-facebook' => [
            'title'   => 'Lancer une publicité Facebook ou Instagram',
            'summary' => 'Une méthode simple pour tester une pub sans brûler ton budget, et mesurer ce qu’elle rapporte vraiment.',
            'minutes' => 5,
            'icon'    => 'chart',
            'steps'   => [
                ['Prépare une vidéo courte', 'Quinze à trente secondes, filmée au téléphone : le problème, le produit en action, le prix et « paiement à la livraison ». Les premières secondes doivent accrocher.'],
                ['Envoie vers ta page produit', 'Pas vers ton profil ni vers WhatsApp seul : vers la page où le client peut commander en une minute. Avec Shopio, c’est le lien de ta page produit.'],
                ['Installe le pixel Meta', 'Il permet à Facebook de savoir qui a commandé et de montrer ta pub à des personnes qui leur ressemblent. Dans Shopio : Marketing → Pixel Meta.'],
                ['Commence petit et cible ta ville', 'Un petit budget par jour pendant quelques jours, dans les villes où tu livres. Ne change pas tout chaque jour : laisse la pub apprendre.'],
                ['Mesure le coût par commande livrée', 'Pas seulement les clics ni les commandes reçues : ce qui compte, c’est ce que tu as encaissé. Note ta dépense dans la page Bénéfices de Shopio pour voir si la pub est rentable.'],
            ],
            'cta'     => ['Suivre tes bénéfices avec Shopio', 'register'],
        ],
        'confirmer-et-reduire-les-refus' => [
            'title'   => 'Confirmer vite et réduire les refus',
            'summary' => 'Les bons réflexes entre la commande et la livraison, pour que le client soit là et paie.',
            'minutes' => 4,
            'icon'    => 'whats',
            'steps'   => [
                ['Appelle ou écris dans l’heure', 'Plus tu attends, plus le client oublie ou achète ailleurs. Dans Shopio, chaque nouvelle commande arrive dans « Non traitée » avec les boutons Appeler et WhatsApp.'],
                ['Confirme le montant et l’adresse exacte', 'Répète le prix total, les frais de livraison, la commune et un repère (« près de la pharmacie »). Les surprises au moment de payer font les refus.'],
                ['Programme quand le client n’est pas prêt', 'S’il demande « samedi », passe la commande en « Programmée » avec la date au lieu de la perdre.'],
                ['Relance ceux qui hésitent', 'Les paniers abandonnés de Shopio te donnent le numéro des clients partis sans commander. Un message poli le jour même suffit souvent.'],
                ['Note pourquoi un colis revient', 'Client injoignable, prix, délai, produit différent… En connaissant la raison, tu sais quoi corriger.'],
            ],
            'cta'     => ['Gérer tes commandes avec Shopio', 'register'],
        ],
        'calculer-son-benefice' => [
            'title'   => 'Calculer ton vrai bénéfice',
            'summary' => 'Le chiffre d’affaires n’est pas ce que tu gagnes. Voici ce qu’il faut retirer, produit par produit.',
            'minutes' => 3,
            'icon'    => 'cash',
            'steps'   => [
                ['Compte seulement ce qui est livré', 'Une commande refusée ne rapporte rien. Ton chiffre d’affaires, c’est l’argent des commandes livrées.'],
                ['Retire le prix d’achat', 'Prix d’achat d’une pièce × nombre de pièces vendues, transport compris si tu importes.'],
                ['Retire la livraison et les retours', 'Ce que tu paies pour chaque livraison, et ce que te coûte chaque colis qui revient.'],
                ['Retire la pub', 'L’argent mis en publicité ce mois-ci pour ce produit.'],
                ['Compare tes produits', 'Certains produits qui se vendent beaucoup rapportent peu, d’autres l’inverse. Mets plus de pub sur ceux qui rapportent vraiment. La page Bénéfices de Shopio fait tout ce calcul pour toi.'],
            ],
            'cta'     => ['Voir tes bénéfices sur Shopio', 'register'],
        ],
    ];

    public static function find(string $slug): ?array
    {
        return self::GUIDES[$slug] ?? null;
    }
}
