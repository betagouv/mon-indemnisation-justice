<?php

declare(strict_types=1);

namespace MonIndemnisationJustice\Controller\Agent;

use MonIndemnisationJustice\Entity\Agent;
use MonIndemnisationJustice\Entity\BrisPorte;
use MonIndemnisationJustice\Entity\DocumentType;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/agent/previsualiser', condition: "env('APP_DEBUG')")]
class PrevisualiserController extends AbstractController
{
    #[IsGranted(Agent::ROLE_AGENT_DOSSIER)]
    #[Route('/dossier/{id}/decision', name: 'agent_previsualiser_courrier_decision', methods: ['GET'])]
    public function previsualiserCourrierDossier(#[MapEntity(id: 'id')] BrisPorte $dossier, Request $request): Response
    {
        $document = $dossier->getDocumentParType(DocumentType::TYPE_COURRIER_MINISTERE);

        if (null === $document) {
            throw new NotFoundHttpException();
        }

        return $this->render($document->getType()->getGabarit(), [
            'dossier' => $document->getDossier(),
            'corps' => $document?->getCorps(),
            'contexte' => $document->getMetaDonnee('contexte') ?? [],
        ]);
    }

    #[IsGranted(Agent::ROLE_AGENT_DOSSIER)]
    #[Route('/dossier/{id}/declaration-acceptation', name: 'agent_previsualiser_declaration_acceptation', methods: ['GET'])]
    public function previsualiserDeclarationAcceptation(#[MapEntity(id: 'id')] BrisPorte $dossier, Request $request): Response
    {
        return $this->render('courrier/declarationAcceptation.html.twig', [
            'dossier' => $dossier,
            'web' => $request->query->getBoolean('w', true),
        ]);
    }

    #[IsGranted(Agent::ROLE_AGENT_DOSSIER)]
    #[Route('/dossier/{id}/arrete-paiement', name: 'agent_previsualiser_courrier_decision', methods: ['GET'])]
    public function previsualiserArretePaiementDossier(#[MapEntity(id: 'id')] BrisPorte $dossier, Request $request): Response
    {
        return $this->render('courrier/arretePaiement.html.twig', [
            'dossier' => $dossier,
            'web' => $request->query->getBoolean('w', true),
        ]);
    }
}
