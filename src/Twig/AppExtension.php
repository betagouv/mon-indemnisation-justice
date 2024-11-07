<?php

namespace App\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;

class AppExtension extends AbstractExtension
{
    public function getFilters(): array
    {
        return [
            new TwigFilter('spellout', [AppRuntime::class, 'spellout']),
            new TwigFilter('est_agent', [AppRuntime::class, 'estAgent']),
            new TwigFilter('est_requerant', [AppRuntime::class, 'estRequerant']),
            new TwigFilter('base64_encode', 'base64_encode'),
            new TwigFilter('base64_decode', 'base64_decode'),
        ];
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('empty_user', [AppRuntime::class, 'emptyUser']),
            new TwigFunction('est_vite_server_actif', [AppRuntime::class, 'estViteServerActif']),
            new TwigFunction('vite_entree_existe', [AppRuntime::class, 'viteEntreeExiste']),
        ];
    }
}
