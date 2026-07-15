<?php

namespace MonIndemnisationJustice\Api\Agent\Fip6\Endpoint\Dossier;

use MonIndemnisationJustice\Api\Agent\Fip6\Output\DossierATransmettreOutput;
use MonIndemnisationJustice\Api\Agent\Fip6\Voter\DossierVoter;
use MonIndemnisationJustice\Entity\Agent;
use MonIndemnisationJustice\Entity\Dossier;
use MonIndemnisationJustice\Entity\EtatDossierType;
use MonIndemnisationJustice\Repository\DossierRepository;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * Route API qui retourne à un agent chargé de la liaison avec le Bureau du Budget la liste des dossiers à transmettre.
 */
#[Route('/api/agent/fip6/dossiers/liste/a-transmettre', name: 'api_agent_dossiers_liste_a_transmettre', methods: ['GET'])]
#[IsGranted(DossierVoter::ACTION_LISTER_A_TRANSMETTRE)]
class ListerDossierATransmettreEndpoint
{
    public function __construct(
        protected readonly DossierRepository $dossierRepository,
        protected readonly NormalizerInterface $normalizer,
    ) {
    }

    public function __invoke(
        Security $security,
    ): Response {
        /** @var Agent $agent */
        $agent = $security->getUser();
        $dossiers = $agent->estRedacteur() ? $agent->getDossiersATransmettreAFIP3() : $this->dossierRepository->listerDossierParEtat(EtatDossierType::DOSSIER_OK_A_INDEMNISER);

        return new JsonResponse(
            $this->normalizer->normalize(
                array_map(
                    fn (Dossier $dossier) => DossierATransmettreOutput::creerDepuisDossier($dossier),
                    $dossiers
                ),
                'json'
            )
        );
    }
}
