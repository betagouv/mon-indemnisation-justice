<?php

declare(strict_types=1);

namespace MonIndemnisationJustice\Controller\Agent;

use MonIndemnisationJustice\Entity\Agent;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/agent/fdo')]
#[IsGranted(Agent::ROLE_AGENT)]
class FdoController extends AbstractController
{
    #[Route('/{extra?}', name: 'agent_fdo_react', requirements: ['extra' => '.*'])]
    public function react(): Response
    {
        return $this->render(
            'agent/fdo.html.twig',
        );
    }
}
