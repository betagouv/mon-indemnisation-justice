<?php

declare(strict_types=1);

namespace MonIndemnisationJustice\Controller\Agent;

use MonIndemnisationJustice\Entity\Agent;
use MonIndemnisationJustice\Entity\BrisPorte;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/agent/previsualiser', condition: "env('APP_DEBUG')")]
class PrevisualiserController extends AbstractController
{
    #[IsGranted(Agent::ROLE_AGENT_DOSSIER)]
    #[Route('/dossier/{id}/decision', name: 'agent_redacteur_courrier_dossier_previsualiser', methods: ['GET'])]
    public function previsualiserCourrierDossier(#[MapEntity(id: 'id')] BrisPorte $dossier, Request $request): Response
    {
        return $this->render('courrier/decision.html.twig', [
            'dossier' => $dossier,
            'web' => $request->query->getBoolean('w', true),
            'formulaire' => $request->query->getBoolean('f', true),
        ]);
    }

    #[IsGranted(Agent::ROLE_AGENT_DOSSIER)]
    #[Route('/dossier/{id}/arrete-paiement', name: 'agent_redacteur_courrier_arrete_paiement_previsualiser', methods: ['GET'])]
    public function previsualiserArretePaiementDossier(#[MapEntity(id: 'id')] BrisPorte $dossier, Request $request): Response
    {
        return $this->render('courrier/arretePaiement.html.twig', [
            'dossier' => $dossier,
            'web' => $request->query->getBoolean('w', true),
        ]);
    }
}
