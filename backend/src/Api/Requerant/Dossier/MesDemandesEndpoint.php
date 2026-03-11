<?php

namespace MonIndemnisationJustice\Api\Requerant\Dossier;

use MonIndemnisationJustice\Api\Requerant\Dossier\Dto\DossierDto;
use MonIndemnisationJustice\Entity\Dossier;
use MonIndemnisationJustice\Entity\Usager;
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
                $usager->getDossiersBrisDePorte()->map(
                    fn (Dossier $dossier) => DossierDto::depuisDossier($dossier)
                )->toArray(),
            ),
            Response::HTTP_OK
        );
    }
}
