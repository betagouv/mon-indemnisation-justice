<?php

declare(strict_types=1);

namespace MonIndemnisationJustice\Controller\Agent;

use MonIndemnisationJustice\Entity\Agent;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/agent/fip6')]
#[IsGranted(Agent::ROLE_AGENT)]
class Fip6Controller extends AbstractController
{
    #[Route('/{extra?}', name: 'agent_fip6_react', requirements: ['extra' => '.*'])]
    public function react(): Response
    {
        /** @var Agent $agent */
        $agent = $this->getUser();

        if ($agent->estFDO()) {
            return $this->redirectToRoute('agent_fdo_react');
        }

        return $this->render(
            'agent/fip6.html.twig',
        );
    }
}
