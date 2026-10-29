<?php

namespace MonIndemnisationJustice\Api\Agent\FDO\Endpoint\BrisDePorte;

use Doctrine\ORM\EntityManagerInterface;
use MonIndemnisationJustice\Api\Agent\FDO\Output\DeclarationFDOBrisPorteOutput;
use MonIndemnisationJustice\Api\Agent\FDO\Voter\DeclarationFDOBrisPorteVoter;
use MonIndemnisationJustice\Entity\DeclarationFDOBrisPorte;
use MonIndemnisationJustice\Entity\Document;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * Route API qui permet à un agent des FDO de supprimer une pièce jointe du brouillon d'une déclaration d'erreur
 * opérationnelle.
 */
#[Route('/api/agent/fdo/bris-de-porte/{declarationId}/piece-jointe/{pieceJointeId}/{hash}/supprimer', name: 'api_agent_fdo_bris_porte_supprimer_piece_jointe', methods: ['DELETE'])]
#[IsGranted(
    DeclarationFDOBrisPorteVoter::ACTION_SUPPRIMER_PJ,
    'declaration',
    message: "La déclaration d'une erreur opérationnelle est retreinte aux agents des Forces de l'Ordre",
    statusCode: Response::HTTP_FORBIDDEN
)]
class SupprimerPieceJointeDeclarationBrisPorteEndpoint
{
    public function __construct(
        protected readonly EntityManagerInterface $em,
        protected readonly NormalizerInterface $normalizer,
    ) {
    }

    public function __invoke(
        #[MapEntity(id: 'declarationId', message: 'Déclaration inconnue')]
        DeclarationFDOBrisPorte $declaration,
        #[MapEntity(id: 'pieceJointeId', message: 'Document inconnu')]
        Document $pieceJointe,
        string $hash,
    ): Response {
        if ($pieceJointe->getFileHash() !== $hash || !$declaration->getPiecesJointes()->contains($pieceJointe)) {
            throw new NotFoundHttpException('Document inconnu');
        }

        $declaration->supprimerPieceJointe($pieceJointe);

        $this->em->persist($declaration);
        $this->em->remove($pieceJointe);
        $this->em->flush();

        return new JsonResponse(
            $this->normalizer->normalize(
                DeclarationFDOBrisPorteOutput::depuisDeclarationFDO($declaration),
                'json'
            ),
            Response::HTTP_CREATED
        );
    }
}
