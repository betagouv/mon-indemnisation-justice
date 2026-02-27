<?php

namespace MonIndemnisationJustice\Api\Requerant\Brouillon;

use MonIndemnisationJustice\Entity\BrouillonType;
use MonIndemnisationJustice\Entity\Usager;
use MonIndemnisationJustice\Service\GestionnaireBrouillon;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/requerant/brouillon/bris-de-porte/initier', name: 'api_requerant_brouillon_bris_porte_initier', methods: ['PUT'])]
class InitierBrouillonEndpoint
{
    public function __construct(
        protected readonly GestionnaireBrouillon $gestionnaireBrouillon,
    ) {
    }

    public function __invoke(Security $security)
    {
        /** @var Usager $requerant */
        $requerant = $security->getUser();

        $this->gestionnaireBrouillon->initier(BrouillonType::BROUILLON_DOSSIER_BRIS_PORTE, requerant: $requerant);

        return new JsonResponse(
            '',
            Response::HTTP_CREATED
        );
    }
}
