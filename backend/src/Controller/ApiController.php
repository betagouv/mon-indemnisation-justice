<?php

declare(strict_types=1);

namespace MonIndemnisationJustice\Controller;

use MonIndemnisationJustice\Entity\GeoCodePostal;
use MonIndemnisationJustice\Repository\GeoCodePostalRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api')]
class ApiController extends AbstractController
{
    public function __construct(
        protected readonly GeoCodePostalRepository $geoCodePostalRepository,
    ) {
    }

    #[Route('/communes/{codePostal}', name: 'api_code_postaux', methods: ['GET'])]
    public function index(string $codePostal): Response
    {
        return new JsonResponse(array_map(
            fn (GeoCodePostal $geoCodePostal) => [
                'id' => $geoCodePostal->getId(),
                'nom' => $geoCodePostal->getCommune()->getNom(),
            ],
            $this->geoCodePostalRepository->findBy(['codePostal' => $codePostal])
        ));
    }
}
