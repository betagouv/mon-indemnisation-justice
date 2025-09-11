<?php

declare(strict_types=1);

namespace MonIndemnisationJustice\Controller\Agent;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/agent/fip3')]
class Fip3Controller extends AbstractController
{
    #[Route('/{extra?}', name: 'agent_fip3_react', requirements: ['extra' => '.*'])]
    public function react(): Response
    {
        return $this->render('agent/fip3.html.twig');
    }
}
