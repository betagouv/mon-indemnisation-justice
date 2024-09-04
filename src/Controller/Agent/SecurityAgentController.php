<?php

declare(strict_types=1);

namespace App\Controller\Agent;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route('/agent')]
class SecurityAgentController extends AbstractController
{
    public function __construct(
        protected readonly AuthenticationUtils $authenticationUtils,
        protected readonly TranslatorInterface $translator,
    ) {
    }

    #[Route(path: '/connexion', name: 'app_agent_securite_connexion', methods: ['GET', 'POST'], options: ['expose' => true])]
    public function connexionAgent(Request $request): Response
    {
        return $this->render('agent/connexion.html.twig', [
            'title' => "Connexion à l'espace agent",
            'last_username' => $this->authenticationUtils->getLastUsername(),
            'has_error' => (bool) $this->authenticationUtils->getLastAuthenticationError(),
        ]);
    }

    #[Route(path: '/deconnexion', name: 'app_agent_securite_deconnexion')]
    public function logout(): void
    {
        throw new \LogicException("Impossible de déconnecter l'agent");
    }
}
