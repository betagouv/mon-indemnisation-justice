<?php

namespace MonIndemnisationJustice\Api\Agent\Fip6\Endpoint\Document;

use Doctrine\ORM\EntityManagerInterface;
use MonIndemnisationJustice\Api\Agent\Fip6\Voter\DocumentVoter;
use MonIndemnisationJustice\Entity\Document;
use MonIndemnisationJustice\Entity\DocumentType;
use MonIndemnisationJustice\Entity\Metadonnees\MetadonneesAttestation;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\ObjectMapper\ObjectMapperInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/api/agent/fip6/document/attestation/{id}/meta-donnees/renseigner', name: 'api_agent_fip6_document-renseigner', methods: ['PUT'])]
#[IsGranted(DocumentVoter::ACTION_RENSEIGNER, 'document', message: "Seul un agent autorisé peut renseigner les méta-données d'une pièce-jointe d'attestation", statusCode: Response::HTTP_FORBIDDEN)]
class RenseignerMetaDonneesAttestationEndpoint
{
    public function __construct(
        protected readonly ObjectMapperInterface $objectMapper,
        protected readonly EntityManagerInterface $em,
    ) {}

    public function __invoke(
        #[MapEntity]
        Document $document,
        #[MapRequestPayload]
        RenseignerMetaDonneesAttestationInput $input,
    ) {
        if (DocumentType::TYPE_ATTESTATION_INFORMATION !== $document->getType()) {
            return new JsonResponse(['erreur' => "Ce document n'est pas une attestation"], Response::HTTP_BAD_REQUEST);
        }

        $metaDonnees = $this->objectMapper->map($input, MetadonneesAttestation::class);
        $document->setMetaDonneesAttestation($metaDonnees);
        $this->em->persist($document);
        $document->getDossier()->recalculerMetaDonnees();
        $this->em->persist($document->getDossier());

        $this->em->flush();

        return new JsonResponse([], Response::HTTP_NO_CONTENT);
    }
}
