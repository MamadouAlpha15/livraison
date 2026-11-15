<?php

namespace App\Console\Commands;

use App\PageBuilder\Templates\ShopioTemplates;
use Illuminate\Console\Command;

/** Installe ou met à jour les modèles de page fournis par Shopio (App\PageBuilder\Templates\ShopioTemplates) */
class SyncPageTemplates extends Command
{
    protected $signature = 'page-templates:sync';

    protected $description = 'Installe ou met à jour les modèles de page produit fournis par Shopio';

    public function handle(): int
    {
        ShopioTemplates::sync();
        $this->info(count(ShopioTemplates::definitions()) . ' modèles Shopio à jour.');
        return self::SUCCESS;
    }
}
