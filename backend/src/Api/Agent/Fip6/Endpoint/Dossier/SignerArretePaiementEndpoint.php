<?php

namespace MonIndemnisationJustice\Api\Agent\Fip6\Endpoint\Dossier;

use MonIndemnisationJustice\Api\Agent\Fip6\Output\DossierDetailOutput;
use MonIndemnisationJustice\Api\Agent\Fip6\Voter\DossierVoter;
use MonIndemnisationJustice\Entity\Agent;
use MonIndemnisationJustice\Entity\DocumentType;
use MonIndemnisationJustice\Entity\Dossier;
use MonIndemnisationJustice\Service\DocumentManager;
use MonIndemnisationJustice\Service\DossierManager;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapUploadedFile;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

#[Route('/api/agent/fip6/dossier/{id}/signer-arrete-paiement', name: 'api_agent_fip6_dossier_signer_arrete_paiement', methods: ['POST'])]
#[IsGranted(DossierVoter::ACTION_INSTRUIRE, 'dossier', message: "Seul l'agent validateur peut signer l'arrêté de paiement", statusCode: Response::HTTP_FORBIDDEN)]
class SignerArretePaiementEndpoint
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
        Security $security,
    ): Response {
        if (null === $dossier->getDocumentParType(DocumentType::TYPE_ARRETE_PAIEMENT)) {
            return new JsonResponse([], Response::HTTP_NOT_FOUND);
        }

        $this->documentManager->ajouterFichierTeleverse(
            $dossier,
            $fichierSigne,
            DocumentType::TYPE_ARRETE_PAIEMENT,
            estAjoutRequerant: false,
        );

        /** @var Agent $agent */
        $agent = $security->getUser();
        $this->dossierManager->avancer($dossier, $agent);

        return new JsonResponse(DossierDetailOutput::creerDepuisDossier($dossier));
    }
}
