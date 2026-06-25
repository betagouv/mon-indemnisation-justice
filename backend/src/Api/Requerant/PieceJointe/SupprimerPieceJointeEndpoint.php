<?php

namespace MonIndemnisationJustice\Api\Requerant\PieceJointe;

use MonIndemnisationJustice\Api\Requerant\Dossier\Dto\DossierDto;
use MonIndemnisationJustice\Api\Requerant\Voter\RequerantDossierVoter;
use MonIndemnisationJustice\Entity\Document;
use MonIndemnisationJustice\Service\DocumentManager;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

#[Route('/api/requerant/piece-jointe/{id}/supprimer', name: 'api_requerant_piece_jointe_supprimer', methods: ['DELETE'])]
#[IsGranted(RequerantDossierVoter::ACTION_DOSSIER_SUPPRIMER_PIECE_JOINTE, 'pieceJointe', message: 'Seul le requérant peut supprimer une pièce jointe de son dossier', statusCode: Response::HTTP_FORBIDDEN)]
class SupprimerPieceJointeEndpoint
{
    public function __invoke(
        #[MapEntity]
        Document $pieceJointe,
        DocumentManager $documentManager,
        NormalizerInterface $normalizer,
    ) {
        $documentManager->supprimer($pieceJointe);

        return new JsonResponse(
            $normalizer->normalize(DossierDto::depuisDossier($pieceJointe->getDossier()), 'json'),
            Response::HTTP_OK
        );
    }
}
