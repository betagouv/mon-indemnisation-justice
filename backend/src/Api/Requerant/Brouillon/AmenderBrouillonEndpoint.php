<?php

namespace MonIndemnisationJustice\Api\Requerant\Brouillon;

use MonIndemnisationJustice\Entity\Brouillon;
use MonIndemnisationJustice\Service\GestionnaireBrouillon;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\Normalizer\AbstractObjectNormalizer;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

#[Route('/api/requerant/brouillon/bris-de-porte/{id}/amender', name: 'api_requerant_brouillon_bris_porte_amender', methods: ['PATCH'])]
class AmenderBrouillonEndpoint
{
    public function __construct(
        protected readonly GestionnaireBrouillon $gestionnaireBrouillon,
        protected readonly NormalizerInterface   $normalizer,
    )
    {
    }

    public function __invoke(#[MapEntity(id: 'id', message: 'Brouillon inconnu')]
                             Brouillon $brouillon,
                             Request   $request)
    {
        $this->gestionnaireBrouillon->amender($brouillon, json_decode($request->getContent(), true));

        return new JsonResponse(
            $this->normalizer->normalize(
                $this->gestionnaireBrouillon->extraireEntiteTravail($brouillon),
                'json',
                [AbstractObjectNormalizer::SKIP_NULL_VALUES => true]
            ),
            Response::HTTP_CREATED
        );
    }


}