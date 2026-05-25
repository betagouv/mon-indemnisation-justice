<?php

namespace MonIndemnisationJustice\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;

class AppExtension extends AbstractExtension
{
    public function getFilters(): array
    {
        return [
            new TwigFilter('montant_litteral', [AppRuntime::class, 'montantLitteral']),
            new TwigFilter('url_deconnexion', [AppRuntime::class, 'urlDeconnexion']),
        ];
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('est_vite_server_actif', [AppRuntime::class, 'estViteServerActif']),
            new TwigFunction('vite_entree_existe', [AppRuntime::class, 'viteEntreeExiste']),
        ];
    }
}
