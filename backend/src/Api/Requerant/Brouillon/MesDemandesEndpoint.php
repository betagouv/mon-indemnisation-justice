<?php

namespace MonIndemnisationJustice\Api\Requerant\Brouillon;

use MonIndemnisationJustice\Entity\Brouillon;
use MonIndemnisationJustice\Entity\BrouillonType;
use MonIndemnisationJustice\Entity\Usager;
use MonIndemnisationJustice\Service\GestionnaireBrouillon;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

#[Route('/api/requerant/mes-demandes', name: 'api_requerant_mes_demandes', methods: ['GET'])]
class MesDemandesEndpoint
{
    public function __construct(
        protected readonly NormalizerInterface $normalizer,
        protected readonly GestionnaireBrouillon $gestionnaireBrouillon,
    ) {
    }

    public function __invoke(Security $security)
    {
        /** @var Usager $usager */
        $usager = $security->getUser();

        if (!$usager instanceof Usager) {
            throw new UnauthorizedHttpException("La liste des demandes d'indemnisation est réservée aux requérants");
        }

        return new JsonResponse(
            $this->normalizer->normalize(
                $usager->getBrouillons()
                    ->filter(fn (Brouillon $brouillon) => BrouillonType::BROUILLON_DOSSIER_BRIS_PORTE === $brouillon->getType())
                    ->map(fn (Brouillon $brouillon) => $this->gestionnaireBrouillon->extraireEntiteTravail($brouillon))
            ),
            Response::HTTP_OK
        );
    }
}
