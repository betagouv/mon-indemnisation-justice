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
            new TwigFilter('est_agent', [AppRuntime::class, 'estAgent']),
            new TwigFilter('est_requerant', [AppRuntime::class, 'estRequerant']),
            new TwigFilter('etat_requerant', [AppRuntime::class, 'etatDossierRequerant']),
            new TwigFilter('url_deconnexion', [AppRuntime::class, 'urlDeconnexion']),
            new TwigFilter('md5', 'md5'),
            new TwigFilter('base64_encode', 'base64_encode'),
            new TwigFilter('base64_decode', 'base64_decode'),
            new TwigFilter('to_snake', [AppRuntime::class, 'toSnake']),
            new TwigFilter('to_kebab', [AppRuntime::class, 'toKebab']),
        ];
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('est_vite_server_actif', [AppRuntime::class, 'estViteServerActif']),
            new TwigFunction('vite_entree_existe', [AppRuntime::class, 'viteEntreeExiste']),
            new TwigFunction('base_64_image', [AppRuntime::class, 'base64Image']),
            new TwigFunction('absolute_asset_path', [AppRuntime::class, 'absoluteAssetPath']),
            new TwigFunction('agent_incarnant', [AppRuntime::class, 'getAgentIncarnant']),
            new TwigFunction('nb_dossiers_a_categoriser', [AppRuntime::class, 'nbDossiersACategoriser']),
            new TwigFunction('nb_dossiers_a_attribuer', [AppRuntime::class, 'nbDossiersAAttribuer']),
            new TwigFunction('nb_dossiers_a_transmettre', [AppRuntime::class, 'nbDossiersATransmettre']),
            new TwigFunction('nb_dossiers_rejet_a_signer', [AppRuntime::class, 'nbDossiersRejetASigner']),
            new TwigFunction('nb_dossiers_proposition_a_signer', [AppRuntime::class, 'nbDossiersPropositionASigner']),
            new TwigFunction('nb_dossiers_arrete_a_signer', [AppRuntime::class, 'nbDossiersArreteASigner']),
            new TwigFunction('nb_dossiers_en_attente_indemnisation', [AppRuntime::class, 'nbDossiersEnAttenteIndemnisation']),
        ];
    }
}
