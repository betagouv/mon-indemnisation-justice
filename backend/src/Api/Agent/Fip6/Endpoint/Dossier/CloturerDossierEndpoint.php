<?php

namespace MonIndemnisationJustice\Api\Agent\Fip6\Endpoint\Dossier;

use MonIndemnisationJustice\Api\Agent\Fip6\Output\EtatDossierOutput;
use MonIndemnisationJustice\Api\Agent\Fip6\Voter\DossierVoter;
use MonIndemnisationJustice\Entity\Agent;
use MonIndemnisationJustice\Entity\Dossier;
use MonIndemnisationJustice\Entity\EtatDossierType;
use MonIndemnisationJustice\Repository\DossierRepository;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

#[Route('/api/agent/fip6/dossier/{id}/cloturer', name: 'api_agent_fip6_dossier_cloturer', methods: ['POST'])]
#[IsGranted(DossierVoter::ACTION_CLOTURER, 'dossier', message: 'Seul un agent autorisé peut clôturer un dossier', statusCode: Response::HTTP_FORBIDDEN)]
class CloturerDossierEndpoint
{
    public function __construct(
        protected readonly NormalizerInterface $normalizer,
        protected readonly DossierRepository $dossierRepository,
    ) {
    }

    public function __invoke(
        #[MapEntity]
        Dossier $dossier,
        #[MapRequestPayload]
        CloturerDossierInput $cloture,
        Security $security,
    ) {
        if (!$dossier->estCloturable()) {
            return new JsonResponse(['erreur' => "Ce dossier n'est pas clôturable"], Response::HTTP_BAD_REQUEST);
        }
        /** @var Agent $agent */
        $agent = $security->getUser();

        $dossier->changerStatut(EtatDossierType::DOSSIER_CLOTURE, agent: $agent, contexte: [
            'motif' => $cloture->motif,
            'explication' => $cloture->explication,
        ]);
        $this->dossierRepository->save($dossier);

        return new JsonResponse([
            'etat' => $this->normalizer->normalize(
                EtatDossierOutput::depuisEtatDossier($dossier->getEtatDossier())
            ),
        ]);
    }
}
