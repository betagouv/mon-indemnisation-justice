<?php

namespace MonIndemnisationJustice\Api\Requerant\Dossier;

use MonIndemnisationJustice\Api\Requerant\Dossier\Dto\DemandeDysfonctionnementDto;
use MonIndemnisationJustice\Api\Requerant\Request\Attribute\MapDossier;
use MonIndemnisationJustice\Api\Requerant\Voter\RequerantDossierVoter;
use MonIndemnisationJustice\Entity\Dossier;
use MonIndemnisationJustice\Entity\DossierType;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

#[Route('/api/requerant/dossier/dysfonctionnement/{reference}', name: 'api_requerant_dossier_dysfonctionnement_consulter', methods: ['GET'])]
#[IsGranted(RequerantDossierVoter::ACTION_DOSSIER_CONSULTER, 'dossier', message: 'Seul le requérant peut consulter son dossier', statusCode: Response::HTTP_FORBIDDEN)]
class ConsulterDossierDysfonctionnementEndpoint
{
    public function __construct(
        protected readonly NormalizerInterface $normalizer,
    ) {
    }

    public function __invoke(
        #[MapDossier(modifie: false, type: DossierType::DYSFONCTIONNEMENT)]
        Dossier $dossier,
    ) {
        return new JsonResponse(
            $this->normalizer->normalize(DemandeDysfonctionnementDto::depuisDossier($dossier), 'json'),
            Response::HTTP_OK
        );
    }
}
