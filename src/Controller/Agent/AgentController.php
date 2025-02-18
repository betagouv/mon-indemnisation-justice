<?php

namespace MonIndemnisationJustice\Controller\Agent;

use MonIndemnisationJustice\Entity\Agent;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class AgentController extends AbstractController
{
    protected function getAgent(): Agent
    {
        $agent = $this->getUser();

        if (!$agent instanceof Agent) {
            throw $this->createAccessDeniedException("Cet espace est réservé aux agents de l'État");
        }

        return $agent;
    }
}
