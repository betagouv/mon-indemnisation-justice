<?php

namespace MonIndemnisationJustice\Api\Requerant\Dossier;

use MonIndemnisationJustice\Api\Requerant\Dossier\Dto\DossierDto;
use MonIndemnisationJustice\Api\Requerant\Request\Attribute\MapDossier;
use MonIndemnisationJustice\Api\Requerant\Voter\RequerantDossierVoter;
use MonIndemnisationJustice\Entity\DocumentType;
use MonIndemnisationJustice\Entity\Dossier;
use MonIndemnisationJustice\Entity\EtatDossierType;
use MonIndemnisationJustice\Service\DocumentManager;
use MonIndemnisationJustice\Service\DossierManager;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapUploadedFile;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/api/requerant/dossier/bris-de-porte/{reference}/accepter-indemnisation', name: 'api_requerant_dossier_bris_porte_accepter_indemnistaion', methods: ['POST'])]
#[IsGranted(RequerantDossierVoter::ACTION_DOSSIER_ACCEPTER_INDEMNISATION, 'dossier', message: "Seul le requérant peut accepter l'indemnisation de son dossier", statusCode: Response::HTTP_FORBIDDEN)]
class AccepterIndemnisationEndpoint
{
    public function __construct(
        protected readonly ValidatorInterface $validator,
        protected readonly DocumentManager $documentManager,
        protected readonly DossierManager $dossierManager,
        protected readonly NormalizerInterface $normalizer,
    ) {
    }

    public function __invoke(
        #[MapDossier(false)]
        Dossier $dossier,
        #[MapUploadedFile(name: 'declarationAcceptationSignee')]
        UploadedFile $declarationAcceptationSignee,
    ) {
        if (EtatDossierType::DOSSIER_OK_A_APPROUVER !== $dossier->getEtatDossier()->getEtat()) {
            return new JsonResponse([
                'erreur' => "Ce dossier n'est pas en attente d'approbation",
            ], Response::HTTP_BAD_REQUEST);
        }

        $this->documentManager->ajouterFichierTeleverse($dossier, $declarationAcceptationSignee, DocumentType::TYPE_COURRIER_REQUERANT);
        $this->dossierManager->avancer($dossier);

        // Générer l'arrêté de paiement
        $this->documentManager->generer($dossier, DocumentType::TYPE_ARRETE_PAIEMENT);

        return new JsonResponse(
            $this->normalizer->normalize(DossierDto::depuisDossier($dossier), 'json'),
            Response::HTTP_OK
        );
    }
}
