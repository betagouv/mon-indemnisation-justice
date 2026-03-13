<?php

namespace MonIndemnisationJustice\Api\Requerant\Dossier;

use Doctrine\ORM\EntityManagerInterface;
use MonIndemnisationJustice\Api\Requerant\Dossier\Dto\DossierDto;
use MonIndemnisationJustice\Api\Requerant\Request\Attribute\MapDossier;
use MonIndemnisationJustice\Api\Requerant\Request\Attribute\MapDossierDto;
use MonIndemnisationJustice\Entity\Dossier;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

#[Route('/api/requerant/dossier/bris-de-porte/{reference}/amender', name: 'api_requerant_dossier_bris_porte_amender', methods: ['PATCH'])]
class AmenderDossierEndpoint
{
    public function __construct(
        protected readonly EntityManagerInterface $em,
        protected readonly NormalizerInterface $normalizer,
    ) {
    }

    public function __invoke(
        #[MapDossier]
        Dossier $dossier,
        #[MapDossierDto]
        DossierDto $dto,
    ) {
        $this->em->persist($dossier);
        $this->em->flush();


        return new JsonResponse(
            $this->normalizer->normalize($dto, 'json'),
            Response::HTTP_OK
        );
    }
}
