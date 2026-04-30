<?php

namespace MonIndemnisationJustice\Api\Requerant\Commune;

use MonIndemnisationJustice\Entity\GeoCodePostal;
use MonIndemnisationJustice\Entity\Usager;
use MonIndemnisationJustice\Repository\GeoCodePostalRepository;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/api/requerant/communes/{codePostal}', name: 'api_requerant_commune_lister', methods: ['GET'])]
#[IsGranted(
    Usager::ROLE_REQUERANT,
    message: 'La recherche de communes par code postal est réservée aux usagers identifiés sur la plateforme',
    statusCode: Response::HTTP_FORBIDDEN
)]
class ListerCommuneEndpoint
{
    public function __construct(
        protected readonly GeoCodePostalRepository $geoCodePostalRepository,
    ) {
    }

    public function __invoke(string $codePostal): JsonResponse
    {
        return new JsonResponse(array_map(
            fn (GeoCodePostal $geoCodePostal) => [
                'id' => $geoCodePostal->getId(),
                'nom' => $geoCodePostal->getCommune()->getNom(),
                'codePostal' => $geoCodePostal->getCodePostal(),
            ],
            $this->geoCodePostalRepository->findBy(['codePostal' => $codePostal])
        ));
    }
}
