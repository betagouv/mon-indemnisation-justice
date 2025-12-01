<?php

declare(strict_types=1);

namespace MonIndemnisationJustice\Controller\Requerant;

use MonIndemnisationJustice\Security\Authenticator\FranceConnectAuthenticator;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class RequerantSecurityController extends AbstractController
{
    #[Route('/requerant/connexion', name: 'requerant_securite_connexion', methods: ['GET'])]
    public function connexionFranceConnect(): Response
    {
        // On ne devrait jamais arriver ici, l'authentificateur France Connect étant configuré pour écouter cette route
        return $this->redirectToRoute('app_login');
    }

    #[Route('/requerant/inscription', name: 'requerant_securite_inscription', methods: ['GET'])]
    public function inscriptionFranceConnect(): Response
    {
        // On ne devrait jamais arriver ici, l'authentificateur France Connect étant configuré pour écouter cette route
        return $this->redirectToRoute('app_login');
    }

    #[Route('/requerant/deconnexion', name: 'requerant_securite_deconnexion', methods: ['GET'])]
    public function deconnexionFranceConnect(Request $request, TokenStorageInterface $tokenStorage, FranceConnectAuthenticator $authenticator): Response
    {
        $authenticator->logout($request);
        $tokenStorage->setToken(null);

        return $this->redirectToRoute('app_logout');
    }
}
