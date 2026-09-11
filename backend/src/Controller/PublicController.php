<?php

namespace MonIndemnisationJustice\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;

class PublicController extends AbstractController
{
    // Toute la SPA `apps/public` (test d'éligibilité, inscription/connexion) part de ce seul rendu Twig, quelle
    // que soit la sous-route affichée par le routeur côté client : les jetons de toutes les intentions utilisées
    // par cette SPA sont donc généré une bonne fois pour toutes ici plutôt qu'à la demande.
    private const array INTENTIONS_CSRF = ['authenticate', 'inscription-dysfonctionnement'];

    #[Route('/dysfonctionnement/tester-mon-eligibilite/{extra}', name: 'public_dysfonctionnement_react', requirements: ['extra' => '.*'], env: ['dev', 'test', 'ci', 'develop'])]
    public function index(CsrfTokenManagerInterface $csrfTokenManager): Response
    {
        return $this->render('public/public.html.twig', [
            'react' => [
                'jetonsCsrf' => array_combine(
                    self::INTENTIONS_CSRF,
                    array_map(
                        fn (string $intention) => $csrfTokenManager->getToken($intention)->getValue(),
                        self::INTENTIONS_CSRF
                    )
                ),
            ],
        ]);
    }
}
