<?php

namespace MonIndemnisationJustice\Api\Agent\FDO\Endpoint\BrisDePorte;

use Doctrine\ORM\EntityManagerInterface;
use MonIndemnisationJustice\Api\Agent\FDO\Input\DeclarationFDOBrisPorteInput;
use MonIndemnisationJustice\Api\Agent\FDO\Input\DocumentDto;
use MonIndemnisationJustice\Api\Agent\FDO\Voter\DeclarationFDOBrisPorteVoter;
use MonIndemnisationJustice\Entity\BrouillonDeclarationFDOBrisPorte;
use MonIndemnisationJustice\Entity\Document;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * Route API qui permet à un agent des FDO de déclarer une erreur opérationnelle.
 */
#[Route('/api/agent/fdo/bris-de-porte/{declarationId}/piece-jointe/{pieceJointeId}/{hash}/supprimer', name: 'api_agent_fdo_bris_porte_supprimer_piece_jointe', methods: ['DELETE'])]
#[IsGranted(
    DeclarationFDOBrisPorteVoter::ACTION_DECLARER,
    message: "La déclaration d'une erreur opérationnelle est retreinte aux agents des Forces de l'Ordre",
    statusCode: Response::HTTP_FORBIDDEN
)]
class SupprimerPieceJointeDeclarationBrisPorteEndpoint
{
    public function __construct(
        protected readonly EntityManagerInterface $em,
        protected readonly NormalizerInterface $normalizer,
        protected readonly DenormalizerInterface $denormalizer,
    ) {}

    public function __invoke(
        #[MapEntity(id: 'declarationId', message: 'Déclaration inconnue')]
        BrouillonDeclarationFDOBrisPorte $brouillon,
        #[MapEntity(id: 'pieceJointeId', message: 'Document inconnu')]
        Document $pieceJointe,
        string $hash,
    ): Response {
        $input = $this->denormalizer->denormalize($brouillon->getDonnees(), DeclarationFDOBrisPorteInput::class, context: [AbstractNormalizer::ALLOW_EXTRA_ATTRIBUTES => false]);

        if (md5($pieceJointe->getFilename()) !== $hash || !in_array($pieceJointe->getId(), array_map(fn (DocumentDto $d) => $d->id === $pieceJointe->getId(), $input->getPiecesJointes()))) {
            throw new NotFoundHttpException('Document inconnu');
        }

        $brouillon->supprimerPieceJointe($pieceJointe);

        $this->em->persist($brouillon);
        $this->em->remove($pieceJointe);
        $this->em->flush();

        $input = $this->denormalizer->denormalize($brouillon->getDonnees(), DeclarationFDOBrisPorteInput::class, context: [AbstractNormalizer::ALLOW_EXTRA_ATTRIBUTES => false]);

        return new JsonResponse(
            $this->normalizer->normalize(
                $input,
                'json',
                // [AbstractObjectNormalizer::SKIP_NULL_VALUES => true]
            ),
            Response::HTTP_CREATED
        );
    }
}
