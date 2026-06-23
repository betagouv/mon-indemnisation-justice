<?php

namespace MonIndemnisationJustice\Api\Agent\Fip6\Endpoint\Agent;

use MonIndemnisationJustice\Entity\Agent;
use MonIndemnisationJustice\Repository\AgentRepository;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * Route API qui retourne la liste des agents rédacteurs.
 */
#[Route('/api/agent/fip6/agents/redacteurs', name: 'api_agent_agents_liste_redacteurs', methods: ['GET'])]
// TODO définir des permissions
// #[IsGranted(AgentVoter::ACTION_READ)]
class ListerRedacteurEndpoint
{
    public function __construct(
        private readonly AgentRepository $agentRepository,
        private readonly NormalizerInterface $normalizer,
    ) {
    }

    public function __invoke()
    {
        return new JsonResponse(
            $this->normalizer->normalize(
                array_map(
                    fn (Agent $agent) => [
                        'id' => $agent->getId(),
                        'nom' => $agent->getNomComplet(capital: true),
                    ],
                    $this->agentRepository->getRedacteurs(),
                ),
                'json'
            )
        );
    }
}
