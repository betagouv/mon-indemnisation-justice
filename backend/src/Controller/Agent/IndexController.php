<?php

declare(strict_types=1);

namespace MonIndemnisationJustice\Controller\Agent;

use MonIndemnisationJustice\Entity\Agent;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/agent')]
#[IsGranted(Agent::ROLE_AGENT)]
class IndexController extends AgentController
{
    #[Route('/', name: 'agent_index')]
    public function index(): Response
    {
        $agent = $this->getAgent();
        if ($agent->hasRole(Agent::ROLE_AGENT_FORCES_DE_L_ORDRE)) {
            return $this->redirectToRoute('agent_fdo_react');
        }

        if ($agent->hasRole(Agent::ROLE_AGENT_DOSSIER)) {
            return $this->redirectToRoute('app_agent_redacteur_accueil');
        }

        return $this->redirectToRoute('agent_mon_compte');
    }

    #[Route('/mon-compte', name: 'agent_mon_compte', methods: ['GET'])]
    public function monCompte(): Response
    {
        return $this->render('agent/mon_compte.html.twig');
    }
}
