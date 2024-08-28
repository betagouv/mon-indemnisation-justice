<?php

declare(strict_types=1);

namespace App\Controller\Agent;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpClient\Exception\ServerException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Contracts\Translation\TranslatorInterface;

class SecuriteController extends AbstractController
{
    public function __construct(
        protected readonly AuthenticationUtils $authenticationUtils,
        protected readonly TranslatorInterface $translator,
    ) {
    }

    #[Route(path: '/agent/connexion', name: 'app_agent_securite_connexion', methods: ['GET', 'POST'], options: ['expose' => true])]
    public function connexionAgent(Request $request): Response
    {
        $error = $this->authenticationUtils->getLastAuthenticationError();
        $lastUsername = $this->authenticationUtils->getLastUsername();

        $errorMessage = '';
        if ($error && $error->getMessage()) {
            $errorMessage = $this->translator->trans($error->getMessage(), [], 'security');
        }

        if (null !== $this->getUser()) {
            return $this->redirect('/redirect');
        }

        return $this->render('agent/connexion.html.twig', [
            'title' => "Connexion à l'espace agent",
            'last_username' => $lastUsername,
            'error_message' => $errorMessage,
        ]);
    }

    #[Route(path: '/agent/deconnexion', name: 'app_agent_securite_deconnexion')]
    public function logout(): void
    {
        throw new ServerException("Impossible de déconnecter l'agent");
    }
}
