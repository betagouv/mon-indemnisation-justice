<?php

namespace MonIndemnisationJustice\Api\Agent\Fip6\Endpoint\Dossier;

use MonIndemnisationJustice\Api\Agent\Fip6\Output\DossierDetailOutput;
use MonIndemnisationJustice\Api\Agent\Fip6\Voter\DossierVoter;
use MonIndemnisationJustice\Entity\Dossier;
use MonIndemnisationJustice\Entity\EtatDossierType;
use MonIndemnisationJustice\Repository\DossierRepository;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

#[Route('/api/agent/fip6/dossier/{id}/transmettre-a-fip3', name: 'api_agent_fip6_dossier_transmettre_a_fip3', methods: ['POST'])]
#[IsGranted(DossierVoter::ACTION_TRANSMETTRE_A_FIP3, subject: 'dossier', message: 'Seul le rédacteur ou un agent habilité peut transmettre les éléments à FIP3', statusCode: Response::HTTP_FORBIDDEN)]
class TransmettreAFIP3Endpoint
{
    public function __construct(
        protected readonly DossierRepository $dossierRepository,
    ) {
    }

    public function __invoke(
        #[MapEntity]
        Dossier $dossier,
        NormalizerInterface $normalizer,
        Security $security,
    ) {
        $dossier->changerStatut(EtatDossierType::DOSSIER_OK_EN_ATTENTE_PAIEMENT, agent: $security->getUser());
        $this->dossierRepository->save($dossier);

        return new JsonResponse(
            $normalizer->normalize(DossierDetailOutput::creerDepuisDossier($dossier), 'json'),
            Response::HTTP_OK
        );
    }
}
