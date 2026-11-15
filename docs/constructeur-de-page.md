# Constructeur de page produit : guide

Ce guide explique comment mettre le constructeur en service, comment un vendeur construit sa page, ce qui est garanti côté client et comment ajouter une section.

## Mise en route sur le serveur

1. `php artisan migrate` : crée les tables `product_pages`, `product_page_versions`, `page_templates` et la colonne `order_items.options`.
2. `npm run build` : compile l'éditeur (`resources/js/page-builder.js`).
3. `php artisan page-templates:sync` : installe les 9 modèles Shopio. Facultatif, car c'est aussi fait au premier passage dans l'éditeur.
4. Envoi de vidéos : régler PHP pour accepter 20 Mo, avec au moins `upload_max_filesize = 25M` et `post_max_size = 30M`.
5. Cache : les pages publiées sont mises en cache avec le cache Laravel configuré (`CACHE_STORE`). Rien d'autre à faire.

## Pour le vendeur

### Où le trouver
Espace vendeur → **Produits** → un produit → **Éditeur de page** (adresse `/products/{id}/editeur`).

Chaque produit a son interrupteur **Visible par les clients**, en haut de la liste des sections, désactivé par défaut. Les clients ne voient la page construite que si elle est publiée et activée ; sinon ils voient la page produit habituelle. Désactiver ne supprime rien : brouillon, version publiée et historique sont gardés.

### L'éditeur
- **Sur ordinateur :** trois panneaux, la liste des sections et blocs, l'aperçu en direct et les réglages de ce qui est sélectionné. Le bouton Mobile / Ordinateur change la taille de l'aperçu.
- **Sur téléphone :** trois onglets, Sections, Aperçu et Réglages.
- **Sélection :** un clic dans l'aperçu sélectionne la section ou le bloc.
- **Organisation :** glisser-déposer par la poignée, ou le menu « … » (monter, descendre, dupliquer, supprimer). L'œil masque un élément.
- **Annuler / rétablir :** Ctrl + Z et Ctrl + Maj + Z, jusqu'à 60 étapes.
- **Enregistrement :** le brouillon s'enregistre tout seul et l'état est affiché en haut. Si la page a été modifiée dans un autre onglet, l'éditeur demande de recharger au lieu d'écraser.
- **Publier :** le brouillon devient la page vue par les clients.

### La section Produit
Toujours présente, une seule fois. Son **formulaire de commande** est obligatoire : il ne peut être ni masqué ni supprimé.

| Bloc | Ce qu'il montre |
|---|---|
| Titre, Prix, Texte | Nom, prix (ancien prix barré seulement s'il est plus élevé), paragraphe libre |
| Note des clients | Moyenne des **vrais** avis, cachée sans avis |
| Compte à rebours | Fin réelle de la vente flash ou date fixe ; disparaît une fois passée, ne repart jamais à zéro |
| Stock restant | Stock **réel**, sous le seuil choisi |
| Clients livrés | Nombre **réel** de commandes livrées, à partir d'un minimum |
| Options du produit | Couleur, taille… (options épuisées désactivées) |
| Offres groupées | Jusqu'à 3 paliers : quantité, remise en % (90 % au plus) ou en montant, cadeau facultatif |
| Quantité | Ignorée quand il y a des offres groupées |
| Produits complémentaires | Jusqu'à 3 produits à cocher, remise facultative |
| Champ personnalisé | Question au client (prénom à graver, pointure…), 5 au plus |
| Formulaire de commande | Nom, téléphone, commune, quartier, récapitulatif, paiement à la livraison |
| Engagements | 3 icônes avec texte |
| Livraison estimée | Dates calculées à partir de tes délais, sans le dimanche si tu le choisis |
| Onglet repliable | Description du produit ou texte libre |

Les réponses aux champs personnalisés, le cadeau et l'économie de l'offre apparaissent dans la **fiche commande** du vendeur.

### Les sections à ajouter

| Catégorie | Sections |
|---|---|
| Produit | Produits de la boutique (les plus récents ou choisis à la main) |
| Médias | Image et texte (dont « Avant / après »), Vidéo (MP4 de 20 Mo et 60 s au plus, YouTube, TikTok) |
| Preuve sociale | Avis clients (vrais avis uniquement), « Vu dans » (affiché seulement après confirmation du vendeur) |
| Conversion | Étapes de commande |
| Contenu | Questions fréquentes, Texte |
| Mise en page | Bannière d'annonce |

Chaque section a un jeu de couleurs (clair, doux, couleur de la boutique, nuit) et des marges séparées pour mobile et ordinateur. Une section ou un bloc sans contenu ne laisse aucun vide chez les clients. Dans l'éditeur, une note explique pourquoi l'élément est caché.

### Modèles, historique, Google
- **Modèles :** 9 modèles Shopio (Classique, Express, Confiance, Promo, Histoire, Mode, Beauté et parfum, Électronique, Produit unique).
  - Appliquer un modèle remplace le brouillon, et « Annuler » ramène la page d'avant.
  - Chaque boutique peut enregistrer ses pages comme modèles, 20 au plus.
- **Historique :** les 10 dernières publications. « Remettre dans le brouillon » ne publie rien.
- **Google :** titre (70 caractères) et description (160 caractères), avec un aperçu du résultat. Laissés vides, ils sont tirés du produit.

### Shopio IA, doublons, code SMS, alerte de stock
- **Shopio IA** (bouton dans l'éditeur) : écrit une bannière, une présentation avec les points forts, des questions fréquentes et le référencement, à partir de la seule fiche produit, avec la consigne de ne rien inventer. Tout va dans le brouillon ; « Annuler » revient en arrière. Relancer remplace la proposition précédente. 20 pages par boutique et par jour. Il faut des crédits sur le compte Anthropic (`ANTHROPIC_API_KEY`).
- **Commandes en double** : même numéro et même produit dans la même boutique, il y a moins de 30 minutes. Le client est averti et peut confirmer une nouvelle commande ; le vendeur voit « Possible doublon » dans la fiche. Aussi sur la page produit habituelle.
- **Historique du client** : dans la fiche commande, nombre de commandes livrées et annulées de ce numéro sur Shopio (aucune boutique ni produit cité). « Client à risque » à partir de 2 annulations quand elles dépassent les livraisons.
- **Code SMS** (réglage du bloc Formulaire de commande) : jamais (par défaut), numéros à risque, au-dessus d'un montant, ou toujours. Code à 6 chiffres, 10 minutes, 5 essais, un envoi par minute et 5 par heure. Sans fournisseur SMS configuré, la vérification est ignorée (les commandes passent) et l'éditeur le signale.
- **Alerte de stock** : un produit épuisé affiche « Me prévenir du retour en stock » (client connecté) ou un lien de connexion qui ramène au produit.

### Brancher Nimba SMS
Dans `.env` : `NIMBA_SERVICE_ID`, `NIMBA_SECRET_TOKEN` et `NIMBA_SENDER_NAME` (nom d'expéditeur approuvé dans le compte Nimba). Sans ces trois valeurs, les SMS sont écrits dans le journal. Une fois branché, **tous** les SMS de l'application partent pour de vrai (codes, notifications de campagnes). Envoyer un premier code de test pour confirmer le format accepté par Nimba.

## Ce qui est garanti côté client

- **Prix :** ils sont toujours recalculés sur le serveur (`App\PageBuilder\Commerce\Checkout`) à partir du prix réel et de la version publiée. Le navigateur n'envoie que des choix. Une offre retirée, un produit non proposé, une réponse hors liste ou un stock insuffisant sont refusés.
- **Stock :** il est verrouillé pendant la commande, pour le produit, l'option, le cadeau et les produits complémentaires.
- **Commande :** mêmes contrôles que la page produit habituelle (boutique vérifiée, mode vacances, nom complet et numéro guinéen pour un invité). Les choix du client sont gardés dans `order_items.options`.
- **Texte riche :** il est nettoyé par HTMLPurifier (liste blanche, sans script, attribut d'évènement, lien `javascript:` ni iframe).
- **Médias :** un vendeur ne peut choisir que ses propres images, vidéos et produits en ligne.
- **Cache :** les visiteurs non connectés reçoivent la page en cache (10 minutes au plus). Elle est recalculée à chaque publication, modification du produit, de ses options ou de la boutique, à la fin d'une vente flash et chaque jour. Le jeton du formulaire est remplacé à chaque affichage.
- **Référencement :** lien canonique, image de partage, données structurées Produit (note uniquement avec de vrais avis) et FAQ.

## Pour les développeurs

### Organisation
- `app/PageBuilder/` : le moteur, avec :
  - `Settings/` (16 types de réglages) ;
  - `SectionType` et `BlockType` ;
  - `Registry` (découverte automatique) ;
  - `PageValidator`, `PageFactory`, `Renderer` (rendu, cache, référencement), `RenderContext` (données réelles du produit) ;
  - `Commerce/Checkout` (offres) ;
  - `Templates/ShopioTemplates`.
- `app/PageBuilder/Sections/` et `Blocks/` : un fichier par type.
- `resources/views/page-builder/` : `layout`, `page`, `_section`, `_block`, `sections/{type}`, `blocks/{type}`.
- `resources/views/vendeur/page-builder/editor.blade.php` et `resources/js/page-builder.js` : l'éditeur.
- `Vendeur\PageBuilderController` (éditeur) et `PageBuilderOrderController` (commande).

### Ajouter une section
1. Créer `app/PageBuilder/Sections/MaSection.php`, qui étend `SectionType` : `type()`, `name()`, `category()`, `settings()`, et au besoin `blocks()`, `presets()`, `maxPerPage()`.
2. Créer `resources/views/page-builder/sections/{type}.blade.php`.
   - Les réglages arrivent dans `$settings` et les blocs visibles dans `$blocks`.
   - Le CSS va dans `@once @push('pb-styles') <style data-pb> … </style> @endpush @endonce`.
3. C'est tout : le registre la découvre, la validation et l'éditeur se construisent à partir du schéma.
4. Règle : ne rien afficher d'inventé. Si une donnée réelle manque, ne rien rendre, et prévoir une note quand `$ctx->editing` est vrai.

### Tests
`tests/Feature/PageBuilder/` contient 7 fichiers et 57 tests :
- **Moteur :** `ModelsTest`, `SchemaTest` ;
- **Éditeur et sections :** `EditorTest`, `SectionsTest` ;
- **Commande :** `CheckoutTest` ;
- **Publication :** `PublishingTest` ;
- **Robustesse :** `HardeningTest` (droits, page de 40 sections, types disparus, fiche commande) ;
- **Page produit habituelle :** `tests/Feature/ProductPage/`.
