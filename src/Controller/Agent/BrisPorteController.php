<?php

declare(strict_types=1);

namespace App\Controller\Agent;

use App\Entity\Agent;
use App\Entity\BrisPorte;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/agent/bris-de-porte')]
#[IsGranted(Agent::ROLE_AGENT_REDACTEUR)]
class BrisPorteController extends AbstractController
{
    #[Route('/consulter/{id}', name: 'agent_bris_porte_consulter', options: ['expose' => true], methods: ['GET', 'POST'])]
    public function consulter(BrisPorte $brisPorte): Response
    {
        return $this->render('agent/bris_porte/consulter_bris_porte.html.twig', [
            'brisPorte' => $brisPorte,
            'prejudice' => $brisPorte,
            'decisionActivee' => true,
            'validationActivee' => in_array(Agent::ROLE_AGENT_VALIDATEUR, $this->getUser()->getRoles()),
        ]);
    }
}
