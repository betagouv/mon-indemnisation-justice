<?php

namespace MonIndemnisationJustice\Api\Agent\Fip6\Endpoint\Dossier;

use MonIndemnisationJustice\Api\Agent\Fip6\Output\DossierDetailOutput;
use MonIndemnisationJustice\Api\Agent\Fip6\Voter\DossierVoter;
use MonIndemnisationJustice\Entity\Dossier;
use MonIndemnisationJustice\Service\DossierManager;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

#[Route('/api/agent/fip6/dossier/{id}/decider', name: 'api_agent_fip6_dossier_decider', methods: ['POST'])]
#[IsGranted(DossierVoter::ACTION_INSTRUIRE, 'dossier', message: "Seul l'agent rédacteur attribué peut instruire un dossier", statusCode: Response::HTTP_FORBIDDEN)]
class DeciderDossierEndpoint
{
    public function __construct(
        protected readonly DossierManager $dossierManager,
        protected readonly NormalizerInterface $normalizer,
    ) {
    }

    public function __invoke(
        #[MapEntity]
        Dossier $dossier,
        #[MapRequestPayload]
        DeciderDossierInput $decision,
        Security $security,
    ) {

        $this->dossierManager->avancer($dossier, $security->getUser(), contexte: null !== $decision->montantIndemnisation
            ? ['montantIndemnisation' => $decision->montantIndemnisation]
            : ['motifRejet' => $decision->motifRejet]);

        return new JsonResponse(DossierDetailOutput::creerDepuisDossier($dossier), Response::HTTP_OK);
    }
}
