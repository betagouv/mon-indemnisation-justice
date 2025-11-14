<?php

declare(strict_types=1);

namespace MonIndemnisationJustice\Controller\Agent;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

#[Route('/agent')]
class SecurityAgentController extends AbstractController
{
    public function __construct(
        protected readonly AuthenticationUtils $authenticationUtils,
    ) {}

    #[Route('/connexion', name: 'agent_securite_connexion', methods: ['GET'])]
    #[IsGranted('PUBLIC_ACCESS')]
    public function connexion(Request $request): Response
    {
        return $this->redirectToRoute('app_login');
    }

    #[Route(path: '/deconnexion', name: 'agent_securite_deconnexion')]
    public function logout(): void
    {
        throw new \LogicException("Impossible de déconnecter l'agent");
    }
}
