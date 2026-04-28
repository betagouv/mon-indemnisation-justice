<?php

namespace MonIndemnisationJustice\Api\Agent\Fip6\Endpoint\Dossier;

use MonIndemnisationJustice\Api\Agent\Fip6\Output\PieceJointeOutput;
use MonIndemnisationJustice\Api\Agent\Fip6\Voter\DossierVoter;
use MonIndemnisationJustice\Entity\Dossier;
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

#[Route('/api/agent/fip6/dossier/{id}/generer-declaration-acceptation', name: 'api_agent_fip6_dossier_generer_declaration_acceptation', methods: ['POST'])]
#[IsGranted(DossierVoter::ACTION_GENERER_DOCUMENT, 'dossier', message: "Seul l'agent rédacteur attribué ou un agent validateur peut générer la déclaration d'acceptation", statusCode: Response::HTTP_FORBIDDEN)]
class GenererDeclarationAcceptationEndpoint
{
    public function __construct(
        protected readonly DocumentManager $documentManager,
        protected readonly NormalizerInterface $normalizer,
        protected readonly ObjectMapperInterface $objectMapper,
    ) {
    }

    public function __invoke(
        #[MapEntity]
        Dossier $dossier,
        #[MapRequestPayload]
        GenererCourrierPropositionIndemnisationInput $input,
    ) {
        $declarationAcceptation = $this->documentManager->generer($dossier, DocumentType::TYPE_COURRIER_REQUERANT, montantIndemnisation: $input->montantIndemnisation);

        return new JsonResponse(
            ['document' => $this->normalizer->normalize(
                $this->objectMapper->map($declarationAcceptation, PieceJointeOutput::class),
                'json'
            )],
            Response::HTTP_CREATED
        );
    }
}
