<?php

declare(strict_types=1);

namespace App\Controller\Agent;

use App\Entity\Agent;
use App\Entity\BrisPorte;
use App\Repository\BrisPorteRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/agent/bris-de-porte')]
#[IsGranted(Agent::ROLE_AGENT_REDACTEUR)]
class BrisPorteController extends AbstractController
{

    public function __construct(
        protected readonly BrisPorteRepository $brisPorteRepository,
    ) {
    }

    #[Route('/consulter/{id}', name: 'agent_bris_porte_consulter', methods: ['GET'])]
    public function consulter(BrisPorte $dossier): Response
    {
        return $this->render('agent/bris_porte/consulter_bris_porte.html.twig', [
            'decompte' => $this->brisPorteRepository->decompteParEtat(),
            'dossier' => $dossier,
        ]);
    }
}
