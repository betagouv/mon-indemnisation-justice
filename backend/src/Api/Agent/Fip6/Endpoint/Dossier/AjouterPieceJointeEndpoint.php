<?php

namespace MonIndemnisationJustice\Api\Agent\Fip6\Endpoint\Dossier;

use Doctrine\ORM\EntityManagerInterface;
use MonIndemnisationJustice\Api\Agent\Fip6\Output\DocumentOutput;
use MonIndemnisationJustice\Api\Agent\Fip6\Voter\DossierVoter;
use MonIndemnisationJustice\Entity\BrisPorte;
use MonIndemnisationJustice\Entity\DocumentType;
use MonIndemnisationJustice\Service\DocumentManager;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapUploadedFile;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\ObjectMapper\ObjectMapperInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * Route API qui permet à un agent FIP6 d'ajouter une pièce jointe à un dossier.
 */
#[Route('/api/agent/fip6/dossier/{id}/ajouter-piece-jointe/{type}', name: 'api_agent_fip6_dossier_ajouter_piece_jointe', methods: ['POST'])]
#[IsGranted(DossierVoter::ACTION_AJOUTER_PIECE_JOINTE, 'dossier', "L'ajout de pièce jointe est réservé à l'agent habilité", Response::HTTP_FORBIDDEN)]
class AjouterPieceJointeEndpoint
{
    public function __construct(
        protected readonly DocumentManager $documentManager,
        protected readonly EntityManagerInterface $em,
        protected readonly NormalizerInterface $normalizer,
        protected readonly ObjectMapperInterface $objectMapper,
    ) {}

    public function __invoke(
        #[MapEntity(id: 'id')]
        BrisPorte $dossier,
        string $type,
        #[MapUploadedFile(name: 'pieceJointe')]
        UploadedFile $pieceJointe
    ): Response {
        if (null === ($documentType = DocumentType::tryFrom($type)) || !$documentType->estPieceJointe()) {
            throw new BadRequestHttpException('Type de pièce jointe non reconnu');
        }

        $document = $this->documentManager->ajouterFichierTeleverse($dossier, $pieceJointe, $documentType);

        $this->em->persist($dossier);
        $this->em->flush();

        return new JsonResponse(
            $this->normalizer->normalize(
                $this->objectMapper->map($document, DocumentOutput::class),
                'json'
            ),
            Response::HTTP_OK
        );
    }
}
