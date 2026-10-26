<?php

namespace App\Support\Campaigns;

/** Commande ou action sur une campagne refusée ; errors() donne les messages à montrer au vendeur. */
class CampaignNotAllowed extends \DomainException
{
    /** @param string[] $errors */
    public function __construct(private array $errors)
    {
        parent::__construct(implode(' ', $errors));
    }

    /** @return string[] */
    public function errors(): array
    {
        return $this->errors;
    }
}
