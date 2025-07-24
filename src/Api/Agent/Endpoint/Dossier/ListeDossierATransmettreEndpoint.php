<?php

namespace MonIndemnisationJustice\Api\Agent\Endpoint\Dossier;

use Doctrine\ORM\EntityManagerInterface;
use MonIndemnisationJustice\Api\Agent\Resources\Output\DossierATransmettreOutput;
use MonIndemnisationJustice\Entity\BrisPorte;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\ObjectMapper\ObjectMapperInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * Route API qui retourne à un agent chargé de la liaison avec le Bureau du Budget la liste des dossiers à transmettre.
 */
#[Route('/api/agent/dossiers/liste/a-transmettre', name: 'api_agent_dossiers_liste_a_transmettre', methods: ['GET'])]
// #[IsGranted('lister', 'dossiers:a-transmettre')]
class ListeDossierATransmettreEndpoint
{
    public function __construct(
        protected readonly ObjectMapperInterface $objectMapper,
        protected readonly EntityManagerInterface $entityManager,
        protected readonly NormalizerInterface $normalizer,
    ) {
    }

    public function __invoke(): Response
    {
        $dossiers = $this->entityManager->getRepository(BrisPorte::class)->getListeDossiersATransmettre();

        return new JsonResponse(
            $this->normalizer->normalize(
                array_map(
                    fn (BrisPorte $dossier) => $this->objectMapper->map($dossier, DossierATransmettreOutput::class),
                    $dossiers
                ),
                'json'
            )
        );
    }
}
