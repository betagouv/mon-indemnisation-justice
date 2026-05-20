<?php

namespace MonIndemnisationJustice\Api\Agent\Fip6\Endpoint\Dossier;

use MonIndemnisationJustice\Api\Agent\Fip6\Output\DossierDetailOutput;
use MonIndemnisationJustice\Api\Agent\Fip6\Voter\DossierVoter;
use MonIndemnisationJustice\Entity\Dossier;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

#[Route('/api/agent/fip6/dossier/{id}', name: 'api_agent_fip6_dossier_consulter', methods: ['GET'])]
#[IsGranted(DossierVoter::ACTION_CONSULTER, subject: 'dossier', message: 'Seul un agent habilité peut consulter un dossier', statusCode: Response::HTTP_FORBIDDEN)]
class ConsulterDossierEndpoint
{
    public function __invoke(
        #[MapEntity]
        Dossier $dossier,
        NormalizerInterface $normalizer,
    ) {
        return new JsonResponse(
            $normalizer->normalize(DossierDetailOutput::creerDepuisDossier($dossier), 'json')
        );
    }
}
