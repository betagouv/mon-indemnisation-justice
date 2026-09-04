<?php

namespace MonIndemnisationJustice\Api\Agent\Fip6\Endpoint\Dossier;

use MonIndemnisationJustice\Api\Agent\Fip6\Output\DossierDetailOutput;
use MonIndemnisationJustice\Api\Agent\Fip6\Voter\DossierVoter;
use MonIndemnisationJustice\Entity\Dossier;
use MonIndemnisationJustice\Repository\DossierRepository;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/api/agent/fip6/dossier/{id}/annoter', name: 'api_agent_fip6_dossier_annoter', methods: ['POST'])]
#[IsGranted(DossierVoter::ACTION_ANNOTER, message: 'Seul un agent autorisé peut annoter le dossier', statusCode: Response::HTTP_FORBIDDEN)]
class AnnoterDossierEndpoint
{
    public function __construct(
        protected readonly DossierRepository $dossierRepository,
    ) {
    }

    public function __invoke(
        #[MapEntity]
        Dossier $dossier,
        #[MapRequestPayload]
        AnnoterDossierInput $entree,
    ) {
        $dossier->setNotes($entree->notes);
        $this->dossierRepository->save($dossier);

        return new JsonResponse(DossierDetailOutput::creerDepuisDossier($dossier));
    }
}
