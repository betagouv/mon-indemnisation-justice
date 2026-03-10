<?php

namespace MonIndemnisationJustice\Api\Requerant\Brouillon;

use MonIndemnisationJustice\Entity\Brouillon;
use MonIndemnisationJustice\Service\GestionnaireBrouillon;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

#[Route('/api/requerant/brouillon/bris-de-porte/{id}/publier', name: 'api_requerant_brouillon_bris_porte_amender', methods: ['POST'])]
class PublierBrouillonEndpoint
{
    public function __construct(
        protected readonly GestionnaireBrouillon $gestionnaireBrouillon,
        protected readonly NormalizerInterface $normalizer,
    ) {
    }

    public function __invoke(
        #[MapEntity(id: 'id', message: 'Brouillon inconnu')]
        Brouillon $brouillon,
        Request $request,
    ) {
        $erreurs = $this->gestionnaireBrouillon->verifier($brouillon);

        if (!empty($erreurs)) {
            return new JsonResponse(
                ['erreurs' => $erreurs],
                Response::HTTP_BAD_REQUEST
            );
        }

        $this->gestionnaireBrouillon->publier($brouillon);

        return new JsonResponse(
            '',
            Response::HTTP_NO_CONTENT
        );
    }
}
