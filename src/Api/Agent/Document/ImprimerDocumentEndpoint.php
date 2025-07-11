<?php

namespace MonIndemnisationJustice\Api\Agent\Document;

use Doctrine\ORM\EntityManagerInterface;
use MonIndemnisationJustice\Entity\Document;
use MonIndemnisationJustice\Service\ImprimanteCourrier;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\ObjectMapper\ObjectMapperInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * Route API qui permet à un agent du bureau du Précontentieux d'éditer le corps d'un document et de générer le PDF via
 * l'impression de la page web _templatée_.
 */
#[Route('/api/agent/document/{id}/imprimer', name: 'api_agent_document_imprimer', methods: ['PUT'])]
class ImprimerDocumentEndpoint
{
    public function __construct(
        protected readonly ImprimanteCourrier $imprimanteCourrier,
        protected readonly ObjectMapperInterface $objectMapper,
        protected readonly EntityManagerInterface $entityManager,
        protected readonly NormalizerInterface $normalizer,
    ) {
    }

    // TODO voir doc https://symfony.com/doc/current/controller/service.html#invokable-controllers
    #[IsGranted('imprimer', 'document', "L'impression est réservée", 401)]
    public function __invoke(
        #[MapEntity(id: 'id')] Document $document,
        #[MapRequestPayload] ImprimerDocumentInput $input,
    ): Response {
        if (!$document->estEditable()) {
            return new JsonResponse([
                'error' => 'Ce document ne peut être édité',
            ],
                Response::HTTP_BAD_REQUEST
            );
        }

        $document = $this->objectMapper->map($input, $document);
        $document = $this->imprimanteCourrier->imprimerDocument($document);
        $document->setAjoutRequerant(false);

        $this->entityManager->persist($document);
        $this->entityManager->flush();

        return new JsonResponse($this->normalizer->normalize($this->objectMapper->map($document, ImprimerDocumentOutput::class), 'json'));
    }
}
