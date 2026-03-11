<?php

namespace MonIndemnisationJustice\Api\Requerant\Dossier;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

#[Route('/api/requerant/dossier/bris-de-porte/{reference}/publier', name: 'api_requerant_dossier_bris_porte_publier', methods: ['POST'])]
class PublierBrouillonEndpoint
{
    public function __construct(
        protected readonly NormalizerInterface $normalizer,
    ) {
    }

    public function __invoke(
        string $reference,
        Request $request,
    ) {
        return new JsonResponse([], Response::HTTP_NOT_IMPLEMENTED);
        /*
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
        */
    }
}
