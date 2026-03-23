<?php

namespace MonIndemnisationJustice\Api\Requerant\Dossier;

use Doctrine\ORM\EntityManagerInterface;
use MonIndemnisationJustice\Api\Requerant\Dossier\Dto\DossierDto;
use MonIndemnisationJustice\Api\Requerant\Request\Attribute\MapDossier;
use MonIndemnisationJustice\Api\Requerant\Request\Attribute\MapDossierDto;
use MonIndemnisationJustice\Api\Requerant\Voter\RequerantDossierVoter;
use MonIndemnisationJustice\Entity\Dossier;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

#[Route('/api/requerant/dossier/bris-de-porte/{reference}', name: 'api_requerant_dossier_bris_porte_consulter', methods: ['GET'])]
#[IsGranted(RequerantDossierVoter::ACTION_DOSSIER_CONSULTER, 'dossier', message: 'Seul le requérant peut consulter son dossier', statusCode: Response::HTTP_FORBIDDEN)]
class ConsulterDossierEndpoint
{
    public function __construct(
        protected readonly EntityManagerInterface $em,
        protected readonly NormalizerInterface $normalizer,
    ) {
    }

    public function __invoke(
        #[MapDossierDto]
        DossierDto $dto,
        #[MapDossier]
        Dossier $dossier,
    ) {
        return new JsonResponse(
            $this->normalizer->normalize($dto, 'json'),
            Response::HTTP_OK
        );
    }
}
