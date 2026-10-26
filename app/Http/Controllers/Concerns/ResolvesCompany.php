<?php

namespace App\Http\Controllers\Concerns;

use App\Models\DeliveryCompany;

/** Société de livraison du compte connecté (propriétaire ou membre), pour l'espace société. */
trait ResolvesCompany
{
    protected function company(): DeliveryCompany
    {
        $company = DeliveryCompany::forUser(auth()->user());
        abort_unless($company, 403, 'Aucune entreprise de livraison liée à ce compte.');
        return $company;
    }
}
