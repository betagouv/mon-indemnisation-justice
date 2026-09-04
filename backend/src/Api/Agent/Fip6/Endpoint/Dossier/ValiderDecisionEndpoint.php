<?php

namespace MonIndemnisationJustice\Api\Agent\Fip6\Endpoint\Dossier;

use MonIndemnisationJustice\Api\Agent\Fip6\Output\DossierDetailOutput;
use MonIndemnisationJustice\Api\Agent\Fip6\Voter\DossierVoter;
use MonIndemnisationJustice\Entity\DocumentType;
use MonIndemnisationJustice\Entity\Dossier;
use MonIndemnisationJustice\Service\DocumentManager;
use MonIndemnisationJustice\Service\DossierManager;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\HttpKernel\Attribute\MapUploadedFile;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

#[Route('/api/agent/fip6/dossier/{id}/valider-decision', name: 'api_agent_fip6_dossier_valider_decision', methods: ['POST'])]
#[IsGranted(DossierVoter::ACTION_VALIDER_DECISION, subject: 'dossier', message: "La validation de la décision est réservée à l'agent validateur", statusCode: Response::HTTP_FORBIDDEN)]
class ValiderDecisionEndpoint
{
    public function __construct(
        protected readonly DocumentManager $documentManager,
        protected readonly DossierManager $dossierManager,
        protected readonly NormalizerInterface $normalizer,
    ) {
    }

    public function __invoke(
        #[MapEntity]
        Dossier $dossier,
        #[MapUploadedFile(name: 'fichierSigne')]
        UploadedFile $fichierSigne,
        #[MapRequestPayload]
        ValiderDecisionInput $decision,
        Security $security,
    ) {
        $this->documentManager->ajouterFichierTeleverse(
            $dossier,
            $fichierSigne,
            DocumentType::TYPE_COURRIER_MINISTERE,
            estAjoutRequerant: false,
        );

        if ($decision->estValide) {
            if ($decision->montantIndemnisation) {
                $dossier->setPropositionIndemnisation($decision->montantIndemnisation);
            }
            $this->dossierManager->avancer(
                $dossier,
                $security->getUser(),
                contexte: null !== $decision->montantIndemnisation ? ['montantIndemnisation' => $decision->montantIndemnisation] : null,
            );
        }
        // TODO gérer le renvoi à l'instruction sinon


        return new JsonResponse(DossierDetailOutput::creerDepuisDossier($dossier), Response::HTTP_OK);
    }
}
