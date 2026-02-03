<?php

namespace MonIndemnisationJustice\Api\Agent\Fip6\Endpoint\Dossier;

use MonIndemnisationJustice\Api\Agent\Fip6\Output\DocumentOutput;
use MonIndemnisationJustice\Api\Agent\Fip6\Voter\DossierVoter;
use MonIndemnisationJustice\Entity\BrisPorte;
use MonIndemnisationJustice\Entity\DocumentType;
use MonIndemnisationJustice\Service\DocumentManager;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\ObjectMapper\ObjectMapperInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

#[Route('/api/agent/fip6/dossier/{id}/generer-courrier-proposition-indemnisation', name: 'api_agent_fip6_dossier_generer_courrier_proposition_indemnisation', methods: ['POST'])]
#[IsGranted(DossierVoter::ACTION_GENERER_DOCUMENT, 'dossier', message: "Seul l'agent rédacteur attribué ou un agent validateur peut générer les courriers d'un dossier", statusCode: Response::HTTP_FORBIDDEN)]
class GenererCourrierPropositionIndemnisationEndpoint
{
    public function __construct(
        protected readonly DocumentManager       $documentManager,
        protected readonly NormalizerInterface   $normalizer,
        protected readonly ObjectMapperInterface $objectMapper,
    )
    {
    }

    public function __invoke(
        #[MapEntity]
        BrisPorte                                    $dossier,
        #[MapRequestPayload]
        GenererCourrierPropositionIndemnisationInput $input,
    )
    {
        $courrier = $this->documentManager->generer($dossier, DocumentType::TYPE_COURRIER_MINISTERE, montantIndemnisation: $input->montantIndemnisation);

        return new JsonResponse(
            ['document' => $this->normalizer->normalize(
                $this->objectMapper->map($courrier, DocumentOutput::class),
                'json'
            )],
            Response::HTTP_CREATED
        );
    }
}
