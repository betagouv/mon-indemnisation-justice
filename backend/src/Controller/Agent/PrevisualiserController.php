<?php

declare(strict_types=1);

namespace MonIndemnisationJustice\Controller\Agent;

use Doctrine\ORM\EntityManagerInterface;
use MonIndemnisationJustice\Entity\Agent;
use MonIndemnisationJustice\Entity\DocumentType;
use MonIndemnisationJustice\Entity\Dossier;
use MonIndemnisationJustice\Entity\EtatDossierType;
use MonIndemnisationJustice\Service\DocumentManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/agent/beta/previsualiser', condition: "env('APP_DEBUG')")]
class PrevisualiserController extends AbstractController
{
    public function __construct(
        protected readonly EntityManagerInterface $em,
        protected readonly DocumentManager $documentManager,
    ) {
    }

    #[IsGranted(Agent::ROLE_AGENT_DOSSIER)]
    #[Route('/proposition-indemnisation/{dossierId}', name: 'agent_beta_previsualiser_proposition_indemnisation', methods: ['GET'])]
    public function previsualiserCourrierDossier(
        Request $request,
        int $dossierId = 0,
    ): Response {
        $dossier = $dossierId > 0 ? $this->em->getRepository(Dossier::class)->find($dossierId) : $this->em->getRepository(Dossier::class)->getDossierParEtat(EtatDossierType::DOSSIER_OK_A_SIGNER);

        $document = $dossier->getOrCreateDocument(DocumentType::TYPE_COURRIER_MINISTERE);

        return new Response(
            $this->documentManager->genererCorps(
                $dossier,
                $request->query->get('montant'),
                montantIndemnisation: $request->query->has('montant') ?
                    floatval($request->query->get('montant')) :
                    $dossier->getMontantIndemnisation() ?? 1234.56
            )
        );
    }
}
