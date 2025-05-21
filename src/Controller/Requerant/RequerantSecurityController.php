<?php

declare(strict_types=1);

namespace MonIndemnisationJustice\Controller\Requerant;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class RequerantSecurityController extends AbstractController
{
    #[Route('/requerant/connexion', name: 'requerant_securite_connexion', methods: ['GET'])]
    public function connexionFranceConnect(): Response
    {
        // On ne devrait jamais arriver ici, l'authentificateur France Connect étant configuré pour écouter cette route
        return $this->render('security/connexion.html.twig');
    }

    #[Route('/requerant/inscription', name: 'requerant_securite_inscription', methods: ['GET'])]
    public function inscriptionFranceConnect(): Response
    {
        // On ne devrait jamais arriver ici, l'authentificateur France Connect étant configuré pour écouter cette route
        return $this->render('security/connexion.html.twig');
    }
}
