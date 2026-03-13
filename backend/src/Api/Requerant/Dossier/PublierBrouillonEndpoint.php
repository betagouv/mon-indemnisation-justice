<?php

namespace MonIndemnisationJustice\Api\Requerant\Dossier;

use MonIndemnisationJustice\Api\Requerant\Dossier\Dto\DossierDto;
use MonIndemnisationJustice\Api\Requerant\Request\Attribute\MapDossier;
use MonIndemnisationJustice\Api\Requerant\Request\Attribute\MapDossierDto;
use MonIndemnisationJustice\Entity\Dossier;
use MonIndemnisationJustice\Service\DossierManager;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/api/requerant/dossier/bris-de-porte/{reference}/publier', name: 'api_requerant_dossier_bris_porte_publier', methods: ['POST', 'PATCH'])]
class PublierBrouillonEndpoint
{
    public function __construct(
        protected readonly ValidatorInterface $validator,
        protected readonly DossierManager $dossierManager,
        protected readonly NormalizerInterface $normalizer,
    ) {
    }

    public function __invoke(
        #[MapDossier]
        Dossier $dossier,
        #[MapDossierDto]
        DossierDto $dto,
    ) {
        $violations = $this->validator->validate($dto);

        if (!empty($erreurs)) {
            return new JsonResponse(
                ['erreurs' => array_merge(
                    ...array_map(
                        fn ($v) => [$v->getPropertyPath() => $v->getMessage()],
                        iterator_to_array($violations->getIterator())
                    )
                )],
                Response::HTTP_BAD_REQUEST
            );
        }

        $this->dossierManager->avancer($dossier);

        return new JsonResponse(
            $this->normalizer->normalize(DossierDto::depuisDossier($dossier), 'json'),
            Response::HTTP_OK
        );
    }
}
