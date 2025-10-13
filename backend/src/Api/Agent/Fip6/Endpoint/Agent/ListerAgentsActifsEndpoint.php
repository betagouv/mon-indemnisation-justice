<?php

namespace MonIndemnisationJustice\Api\Agent\Fip6\Endpoint\Agent;

use MonIndemnisationJustice\Api\Agent\Fip6\Output\AgentOutput;
use MonIndemnisationJustice\Entity\Agent;
use MonIndemnisationJustice\Repository\AgentRepository;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\ObjectMapper\ObjectMapperInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * Route API qui retourne à un agent chargé de l'attribution la liste des dossiers à assigner à un rédacteur pour
 * instruction.
 */
#[Route('/api/agent/fip6/agents/actifs', name: 'api_agent_agents_liste_actifs', methods: ['GET'])]
// #[IsGranted(AgentVoter::ACTION_LISTER_ACTIFS)]
class ListerAgentsActifsEndpoint
{
    public function __construct(
        private readonly AgentRepository $agentRepository,
        private readonly NormalizerInterface $normalizer,
        private readonly ObjectMapperInterface $mapper,
    ) {}

    public function __invoke()
    {
        return new JsonResponse(
            $this->normalizer->normalize(
                array_map(
                    fn (Agent $agent) => $this->mapper->map($agent, AgentOutput::class),
                    $this->agentRepository->getActifs(),
                ),
                'json'
            )
        );
    }
}
