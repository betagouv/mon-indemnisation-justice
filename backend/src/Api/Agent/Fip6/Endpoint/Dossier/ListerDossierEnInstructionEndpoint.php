<?php

namespace MonIndemnisationJustice\Api\Agent\Fip6\Endpoint\Dossier;

use Doctrine\ORM\EntityManagerInterface;
use MonIndemnisationJustice\Api\Agent\Fip6\Output\DossierEnInstructionOutput;
use MonIndemnisationJustice\Api\Agent\Fip6\Voter\DossierVoter;
use MonIndemnisationJustice\Entity\Agent;
use MonIndemnisationJustice\Entity\Dossier;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * Route API qui retourne à un agent rédacteur la liste de ses dossiers en cours d'instruction.
 */
#[Route('/api/agent/fip6/dossiers/liste/en-instruction', name: 'api_agent_dossiers_liste_en_instruction', methods: ['GET'])]
#[IsGranted(DossierVoter::ACTION_LISTER_A_INSTRUIRE)]
class ListerDossierEnInstructionEndpoint
{
    public function __construct(
        protected readonly EntityManagerInterface $entityManager,
        protected readonly NormalizerInterface $normalizer,
    ) {
    }

    public function __invoke(Security $security): Response
    {
        /** @var Agent $agent */
        $agent = $security->getUser();

        return new JsonResponse(
            $this->normalizer->normalize(
                array_map(
                    fn (Dossier $dossier) => DossierEnInstructionOutput::creerDepuisDossier($dossier),
                    array_values($agent->getDossiersEnInstruction())
                ),
                'json',
            )
        );
    }
}
