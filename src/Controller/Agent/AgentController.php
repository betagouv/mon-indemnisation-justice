<?php

declare(strict_types=1);

namespace MonIndemnisationJustice\Controller\Agent;

use MonIndemnisationJustice\Entity\Agent;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/agent')]
#[IsGranted(Agent::ROLE_AGENT)]
class AgentController extends AbstractController
{

    #[Route('/', name: 'agent_index')]
    public function index(): Response
    {
        if (in_array(Agent::ROLE_AGENT_REDACTEUR, $this->getUser()->getRoles())) {
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
