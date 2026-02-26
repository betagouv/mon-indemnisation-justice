<?php

declare(strict_types=1);

namespace MonIndemnisationJustice\Controller\Agent;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Attribute\Route;

class SecurityAgentController extends AbstractController
{
    #[Route(path: '/agent/deconnexion', name: 'agent_securite_deconnexion')]
    public function logout(): void
    {
        throw new \LogicException("Impossible de déconnecter l'agent");
    }
}
