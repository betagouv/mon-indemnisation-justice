<?php

namespace MonIndemnisationJustice\Api\Agent\Fip6\Endpoint\Dossier;

use MonIndemnisationJustice\Api\Agent\Fip6\Output\DossierEnAttenteIndemnisationOutput;
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
#[Route('/api/agent/fip6/dossiers/liste/en-attente-indemnisation', name: 'api_agent_dossiers_en_attente_indemnisation', methods: ['GET'])]
#[IsGranted(DossierVoter::ACTION_LISTER_EN_ATTENTE_INDEMNISATION)]
class ListerDossierEnAttenteIndemnisationEndpoint
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
        $dossiers = $agent->estRedacteur() ? $agent->getDossiersEnAttentePaiement() : $this->dossierRepository->listerDossierParEtat(EtatDossierType::DOSSIER_OK_EN_ATTENTE_PAIEMENT);

        return new JsonResponse(
            $this->normalizer->normalize(
                array_map(
                    fn (Dossier $dossier) => DossierEnAttenteIndemnisationOutput::creerDepuisDossier($dossier),
                    $dossiers
                ),
                'json'
            )
        );
    }
}
