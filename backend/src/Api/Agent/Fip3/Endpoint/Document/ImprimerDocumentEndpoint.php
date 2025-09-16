<?php

namespace MonIndemnisationJustice\Api\Agent\Fip3\Endpoint\Document;

use Doctrine\ORM\EntityManagerInterface;
use MonIndemnisationJustice\Api\Agent\Fip3\Output\DocumentOutput;
use MonIndemnisationJustice\Api\Agent\Fip3\Voter\DocumentVoter;
use MonIndemnisationJustice\Entity\Document;
use MonIndemnisationJustice\Service\ImprimanteCourrier;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\ObjectMapper\ObjectMapperInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * Route API qui permet à un agent FIP3 d'éditer le corps d'un document et de générer le PDF via
 * l'impression de la page web _templatée_.
 */
#[Route('/api/agent/fip3/document/{id}/imprimer', name: 'api_agent_document_imprimer', methods: ['PUT'])]
#[IsGranted(DocumentVoter::ACTION_IMPRIMER, 'document', "L'impression est réservée", 401)]
class ImprimerDocumentEndpoint
{
    public function __construct(
        protected readonly ImprimanteCourrier $imprimanteCourrier,
        protected readonly ObjectMapperInterface $objectMapper,
        protected readonly EntityManagerInterface $entityManager,
        protected readonly NormalizerInterface $normalizer,
    ) {}

    public function __invoke(
        #[MapEntity]
        Document $document,
        #[MapRequestPayload]
        ImprimerDocumentInput $input,
    ): Response {
        if (!$document->estEditable()) {
            throw new BadRequestHttpException('Ce document ne peut être édité');
        }

        $document = $this->objectMapper->map($input, $document);
        $document = $this->imprimanteCourrier->imprimerDocument($document);
        $document->setAjoutRequerant(false);

        $this->entityManager->persist($document);
        $this->entityManager->flush();

        return new JsonResponse($this->normalizer->normalize($this->objectMapper->map($document, DocumentOutput::class), 'json'));
    }
}
