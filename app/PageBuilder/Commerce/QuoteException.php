<?php

namespace App\PageBuilder\Commerce;

/** Choix du client impossible à honorer (offre retirée, quantité, produit plus proposé) : message pour le client */
class QuoteException extends \RuntimeException
{
}
