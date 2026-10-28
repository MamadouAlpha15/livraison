# Relances et Shopio IA : guide

Ce guide explique comment régler les relances automatiques, remplir la FAQ, régler Shopio IA et suivre son coût.

## Mise en route sur le serveur

1. `php artisan migrate` : crée les tables des relances et de Shopio IA, avec la FAQ de départ.
2. Le cron `* * * * * php artisan schedule:run` doit tourner. `followups:run` passe toutes les 10 minutes.
3. Un **worker** `php artisan queue:work` doit tourner en continu. Sans lui, aucune relance ni aucune réponse de l'IA ne part.
4. La clé `ANTHROPIC_API_KEY=...` va dans le `.env`. Sans clé :
   - « Générer avec Shopio IA » affiche un message d'erreur ;
   - en mode automatique, le client reçoit le message de secours « Le vendeur te répond très vite ».

## Les relances

### Où les régler
- **Vendeur :** espace vendeur → **Relances**. Chaque situation s'active d'un geste ; « Régler » ouvre le détail.
- **Admin :** Admin → **Relances** → « Vendeurs inactifs ».

### Les 7 situations
| Situation | Pour qui | Réglage de départ |
|---|---|---|
| Offre de prix sans réponse | client | activée, après 2 h, 2 relances, 1 jour entre les deux |
| Contre-offre sans réponse | client | activée, après 2 h, 2 relances |
| Conversation abandonnée | client | désactivée, après 1 jour |
| Commande à confirmer | **vendeur** | activée, après 1 h, 2 rappels |
| Client qui ne commande plus | client | désactivée, après 30 jours |
| Produit de nouveau en stock | client | activée, tout de suite ; remplace la notification simple |
| Vendeur inactif (admin) | vendeur | activée, après 14 jours, 3 rappels |

### Le message
- **Variables :** {prenom_client}, {nom_boutique}, {produit}, {prix}, {prix_negocie}, {lien_produit}, {lien_commande}, {lien_boutique}, {code_promo}, {date_limite}.
- **Phrases optionnelles :** une phrase entre `[[ ]]` disparaît si une de ses variables est vide. Exemple : `[[ Code promo : {code_promo}.]]` disparaît quand aucun code n'est choisi.
- **Ton :** amical (tutoiement), professionnel (vouvoiement) ou urgent. Si le texte par défaut n'est pas modifié, il suit le ton et la langue choisis.
- **Aides :** « Générer avec Shopio IA » propose un message à relire. L'aperçu montre le résultat avec les données d'exemple de la boutique.
- **Désinscription :** la phrase « Réponds STOP… » est ajoutée toute seule aux messages envoyés aux clients.

### Les règles appliquées automatiquement
- **Arrêt :** la relance s'arrête dès que le client répond, accepte ou refuse l'offre, commande, ou quand la commande est confirmée ou annulée.
- **Envoi :** pas d'envoi en dehors des heures choisies (heure de Conakry) ni les jours exclus. La relance attend alors le prochain créneau.
- **Une par jour :** une seule relance par personne et par jour, **toutes boutiques confondues**. Les rappels « Tu nous manques » comptent aussi.
- **STOP :** un client qui écrit STOP n'est plus jamais relancé par cette boutique. Le lien de désinscription arrête les relances de toutes les boutiques.
- **Jamais en double :** chaque relance est enregistrée une seule fois (clé unique) et verrouillée pendant l'envoi.
- **Statistiques :** relances envoyées, taux de réponse, commandes récupérées (commande du client dans les 7 jours qui suivent une relance) et montant en GNF.
- **Canaux :**
  - la notification dans l'appli et le message dans la messagerie fonctionnent ;
  - les SMS sont seulement écrits dans le journal tant qu'aucun fournisseur n'est branché ;
  - WhatsApp est prévu mais désactivé (`services.whatsapp.enabled`).

## La FAQ de Shopio

Admin → **Shopio IA** → « FAQ de Shopio ».
- **Contenu de départ :** 6 questions, rédigées uniquement à partir de faits présents dans le code : paiement à la livraison, offres, compte, plans Pro et Business, parrainage.
- **À compléter :** il n'existe pas encore d'entrée sur les **retours** ni la **livraison** au niveau de Shopio. Ajoute-les si tu as une règle commune.
- **Prise en compte :** une question décochée (« Utilisée par Shopio IA ») n'est plus lue par l'IA. Les modifications sont prises en compte tout de suite.

## Régler Shopio IA (vendeur)

Espace vendeur → **Shopio IA**.
- **Activation et mode :**
  - « Me proposer une réponse » : le vendeur l'envoie en un clic, après l'avoir modifiée s'il le souhaite ;
  - « Répondre tout seul » : la réponse part directement, marquée « Shopio IA ». Ce mode est réservé au plan Pro.
- **Tutoiement ou vouvoiement**, et ton souhaité.
- **Remise que l'IA peut accorder seule :**
  - vide : l'IA transmet seulement les propositions de prix au vendeur ;
  - avec un pourcentage : une proposition dans la limite devient une offre que le client confirme.
- **Infos de la boutique :** horaires, livraison, retours, réponses fréquentes. Ce qui reste vide, l'IA dit qu'elle ne le sait pas.
- **Conversations :** « Reprendre la main » fait taire l'IA pour ce client, « Rendre la main à l'IA » la relance. Quand le vendeur écrit lui-même, l'IA se tait 2 h dans cette conversation.

### Ce que l'IA ne fait jamais
- **Inventer :** elle n'invente ni prix, ni stock, ni délai, ni promotion. Une réponse qui cite un montant, un stock ou un pourcentage absent des données lues est **bloquée**. Le client reçoit « Je préfère vérifier avec le vendeur » et la conversation passe au vendeur.
- **Accepter un prix seule :** jamais, sauf dans la limite de remise fixée par le vendeur.
- **Montrer d'autres commandes :** un client ne voit que les siennes.
- **Insister :** elle passe au vendeur si le client le demande, s'il est mécontent, en cas de litige ou de remboursement, ou si elle n'est pas sûre.

## Suivre le coût

Admin → **Shopio IA** :
- **Coût par boutique et par mois :** messages, tokens et coût estimé. Le tarif de Claude Opus 5.5 est de 4 $ par million de tokens en entrée et 20 $ en sortie, soit environ **0,03 $ par réponse avec outils**.
- **Limites :** par défaut, 50 messages par mois au plan gratuit (suggestions seulement) et 1 000 au plan Pro. Au-delà, l'IA se tait jusqu'au mois suivant.
- **Interrupteur général :** il coupe Shopio IA pour toutes les boutiques.
- **Journal des outils :** les 40 derniers appels, pour vérifier d'où vient une réponse. Il ne contient ni téléphone ni adresse.
