<?php

namespace MonIndemnisationJustice\Api\Agent\Fip6\Endpoint\Dossier;

use Doctrine\ORM\EntityManagerInterface;
use MonIndemnisationJustice\Api\Agent\Fip6\Output\DossierAAttribuerOutput;
use MonIndemnisationJustice\Api\Agent\Fip6\Voter\DossierVoter;
use MonIndemnisationJustice\Entity\Dossier;
use MonIndemnisationJustice\Entity\EtatDossierType;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * Route API qui retourne à un agent chargé de l'attribution la liste des dossiers à assigner à un rédacteur pour
 * instruction.
 */
#[Route('/api/agent/fip6/dossiers/liste/a-attribuer', name: 'api_agent_dossiers_liste_a_attribuer', methods: ['GET'])]
#[IsGranted(DossierVoter::ACTION_LISTER_A_ATTRIBUER)]
class ListerDossierAAttribuerEndpoint
{
    public function __construct(
        protected readonly EntityManagerInterface $entityManager,
        protected readonly NormalizerInterface $normalizer,
    ) {
    }

    public function __invoke(): Response
    {
        $dossiers = $this->entityManager
            ->getRepository(Dossier::class)
            ->listerDossierParEtat(EtatDossierType::DOSSIER_A_ATTRIBUER);

        return new JsonResponse(
            $this->normalizer->normalize(
                array_map(
                    fn (Dossier $dossier) => DossierAAttribuerOutput::depuisDossier($dossier),
                    $dossiers
                ),
                'json'
            )
        );
    }
}
