<?php

namespace MonIndemnisationJustice\Api\Requerant\Dossier;

use MonIndemnisationJustice\Api\Requerant\Dossier\Dto\DossierDto;
use MonIndemnisationJustice\Api\Requerant\Request\Attribute\MapDossier;
use MonIndemnisationJustice\Api\Requerant\Voter\RequerantDossierVoter;
use MonIndemnisationJustice\Entity\DocumentType;
use MonIndemnisationJustice\Entity\Dossier;
use MonIndemnisationJustice\Service\DocumentManager;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

#[Route('/api/requerant/dossier/bris-de-porte/{reference}/televerser-pieces-jointes', name: 'api_requerant_dossier_bris_porte_televerser_pieces_jointes', methods: ['POST'])]
#[IsGranted(RequerantDossierVoter::ACTION_DOSSIER_TELEVERSER, 'dossier', message: 'Seul le requérant peut téléverser des pièces jointes à son dossier', statusCode: Response::HTTP_FORBIDDEN)]
class TeleverserPiecesJointesEndpoint
{
    public function __construct(
        protected readonly DocumentManager $documentManager,
        protected readonly NormalizerInterface $normalizer,
    ) {
    }

    public function __invoke(
        #[MapDossier(modifie: false)]
        Dossier $dossier,
        Request $request,
    ) {
        /** @var UploadedFile[] $piecesJointes */
        $piecesJointes = $request->files->get('piecesJointes');

        if (empty($piecesJointes)) {
            throw new BadRequestHttpException("Aucune pièce jointe n'a été envoyée");
        }

        /** @var array $donnees */
        $donnees = json_decode($request->request->get('donnees'), true);
        if (!is_array($donnees) || count($donnees) != count($piecesJointes)) {
            throw new BadRequestHttpException('Les nombres de pièces jointes et de types associés ne correspondent pas');
        }

        foreach ($piecesJointes as $index => $pieceJointe) {
            $this->documentManager->ajouterFichierTeleverse($dossier, $pieceJointe, DocumentType::tryFrom($donnees[$index]['type']), estAjoutRequerant: true);
        }

        return new JsonResponse(
            $this->normalizer->normalize(DossierDto::depuisDossier($dossier), 'json'),
            Response::HTTP_OK
        );
    }
}
