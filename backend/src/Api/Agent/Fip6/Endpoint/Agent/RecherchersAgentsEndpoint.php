<?php

namespace MonIndemnisationJustice\Api\Agent\Fip6\Endpoint\Agent;

use MonIndemnisationJustice\Api\Agent\Fip6\Output\AgentOutput;
use MonIndemnisationJustice\Api\Agent\Fip6\Voter\AgentVoter;
use MonIndemnisationJustice\Entity\Agent;
use MonIndemnisationJustice\Repository\AgentRepository;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\MapQueryString;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * Route API qui retourne à un agent chargé de l'attribution la liste des dossiers à assigner à un rédacteur pour
 * instruction.
 */
#[Route('/api/agent/fip6/agents/rechercher', name: 'api_agent_agents_rechercher', methods: ['GET'])]
#[IsGranted(AgentVoter::ACTION_RECHERCHER)]
class RecherchersAgentsEndpoint
{
    public function __invoke(
        #[MapQueryString] RechercherAgentInput $input,
        AgentRepository $agentRepository,
        NormalizerInterface $normalizer,
    ) {
        $paginator = $agentRepository->rechercherAgents(
            page: $input->page,
            taille: $input->taille,
            actifs: $input->actif,
            administrations: $input->administrations ?? [],
            recherche: $input->recherche
        );

        return new JsonResponse(
            [
                'page' => $input->page,
                'taille' => $input->taille,
                'total' => $paginator->count(),
                'resultats' => $normalizer->normalize(
                    array_map(
                        fn (Agent $agent) => AgentOutput::depuisAgent($agent),
                        iterator_to_array(
                            $paginator->getIterator()
                        ) ?? [],
                    ),
                    'json'
                ),
            ]
        );
    }
}
