<?php

// ============================================================
// FICHIER : app/Http/Controllers/Controller.php
// RÔLE    : Classe de base dont TOUS les autres controllers héritent.
//           Elle regroupe des fonctionnalités communes partagées
//           par tous les controllers de l'application.
// ============================================================

// On dit à PHP dans quel "dossier logique" (namespace) se trouve ce fichier.
// Cela permet à Laravel de trouver automatiquement ce fichier sans qu'on l'importe manuellement.
namespace App\Http\Controllers;

// On importe le trait qui permet d'utiliser $this->authorize() dans les controllers
// (vérifier si l'utilisateur a le droit de faire une action)
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

// On importe le trait qui permet d'utiliser $this->validate() dans les controllers
// (vérifier que les données envoyées par l'utilisateur sont correctes)
use Illuminate\Foundation\Validation\ValidatesRequests;

// On importe la classe Controller de Laravel dont on va hériter
use Illuminate\Routing\Controller as BaseController;

// On importe Auth pour pouvoir récupérer l'utilisateur connecté
use Illuminate\Support\Facades\Auth;

// ============================================================
// Déclaration de la classe Controller
// "extends BaseController" = cette classe hérite de la classe
// Controller de Laravel (elle récupère toutes ses fonctionnalités)
// ============================================================
class Controller extends BaseController
{
    // "use" ici signifie qu'on "colle" les fonctionnalités de ces deux traits
    // dans cette classe. C'est comme copier-coller leur code ici.
    // - AuthorizesRequests : ajoute la méthode authorize() pour vérifier les droits
    // - ValidatesRequests  : ajoute la méthode validate() pour vérifier les données
    use AuthorizesRequests, ValidatesRequests;

    // ============================================================
    // MÉTHODE : currentShopId()
    // RÔLE    : Retourne l'identifiant (ID) de la boutique
    //           associée à l'utilisateur connecté.
    //           Retourne null si l'utilisateur n'a pas de boutique.
    // RETOUR  : int (l'ID de la boutique) ou null
    // ============================================================
    protected function currentShopId(): ?int
    {
        // On récupère l'utilisateur actuellement connecté
        $u = Auth::user();

        // On essaie d'abord $u->shop_id (champ direct sur la table users)
        // Si shop_id est null ou vide, on cherche via la relation $u->shop (objet Shop lié)
        // Le "?->" (nullsafe operator) évite une erreur si $u est null (personne connectée)
        // Le "optional()" évite une erreur si $u->shop est null
        return $u?->shop_id ?: optional($u?->shop)->id;
    }
}
