<?php

namespace MonIndemnisationJustice\Api\Agent\Fip6\Endpoint\Dossier;

use MonIndemnisationJustice\Api\Agent\Fip6\Output\DocumentOutput;
use MonIndemnisationJustice\Api\Agent\Fip6\Voter\DossierVoter;
use MonIndemnisationJustice\Entity\BrisPorte;
use MonIndemnisationJustice\Service\DocumentManager;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\ObjectMapper\ObjectMapperInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

#[Route('/api/agent/fip6/dossier/{id}/generer-arrete-paiement', name: 'api_agent_fip6_dossier_generer_arrete_paiement', methods: ['POST'])]
#[IsGranted(DossierVoter::ACTION_GENERER_DOCUMENT, 'dossier', message: "Seul l'agent rédacteur attribué ou un agent validateur peut générer les courriers d'un dossier", statusCode: Response::HTTP_FORBIDDEN)]
class GenererArretePaiementEndpoint
{
    public function __construct(
        protected readonly DocumentManager $documentManager,
        protected readonly NormalizerInterface $normalizer,
        protected readonly ObjectMapperInterface $objectMapper,
    ) {}

    public function __invoke(
        #[MapEntity]
        BrisPorte $dossier,
    ) {
        try {
            $this->documentManager->genererArretePaiement($dossier);
        } catch (\Throwable $e) {
            return new JsonResponse([
                'error' => $e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        return new JsonResponse(
            ['document' => $this->normalizer->normalize(
                $this->objectMapper->map($dossier->getArretePaiement(), DocumentOutput::class),
                'json'
            )],
            Response::HTTP_CREATED
        );
    }
}
