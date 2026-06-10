<?php

namespace MonIndemnisationJustice\Twig;

use Doctrine\ORM\EntityManagerInterface;
use MonIndemnisationJustice\Entity\Usager;
use MonIndemnisationJustice\Security\Authenticator\FranceConnectAuthenticator;
use MonIndemnisationJustice\Service\MontantAfficheur;
use Pentatrion\ViteBundle\Exception\EntrypointNotFoundException;
use Pentatrion\ViteBundle\Service\EntrypointsLookup;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Http\FirewallMapInterface;
use Twig\Extension\RuntimeExtensionInterface;

class AppRuntime implements RuntimeExtensionInterface
{
    protected string $publicDirectory;

    public function __construct(
        protected readonly EntrypointsLookup $entrypointLookup,
        #[Autowire(param: 'kernel.project_dir')]
        string $projectDirectory,
        protected readonly EntityManagerInterface $em,
        protected readonly UrlGeneratorInterface $router,
        protected readonly FranceConnectAuthenticator $franceConnectAuthenticator,
        protected readonly FirewallMapInterface $firewallMap,
        protected readonly Security $security,
        protected readonly MontantAfficheur $montantAfficheur,
    ) {
        $this->publicDirectory = "{$projectDirectory}/public";
    }

    public function urlDeconnexion(Usager $requerant, Request $request): ?string
    {
        if ($requerant->estFranceConnect() && null !== ($logoutUrl = $this->franceConnectAuthenticator->getUrlDeconnexion($request))) {
            return $logoutUrl;
        }

        return $this->router->generate('securite_usager_deconnexion');
    }

    public function montantLitteral(float $amount): string
    {
        return $this->montantAfficheur->afficherMontantLitteral($amount);

    }

    public function estViteServerActif(): bool
    {
        return null !== $this->entrypointLookup->getViteServer();
    }

    public function viteEntreeExiste(string $entree): bool
    {
        try {
            return !empty($this->entrypointLookup->getJSFiles($entree));
        } catch (EntrypointNotFoundException $e) {
            return false;
        }
    }
}
