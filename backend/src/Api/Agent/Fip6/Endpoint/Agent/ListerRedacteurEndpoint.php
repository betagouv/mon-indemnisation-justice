<?php

namespace MonIndemnisationJustice\Api\Agent\Fip6\Endpoint\Agent;

use MonIndemnisationJustice\Api\Agent\Fip6\Voter\AgentVoter;
use MonIndemnisationJustice\Entity\Agent;
use MonIndemnisationJustice\Repository\AgentRepository;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * Route API qui retourne la liste des agents rédacteurs.
 */
#[Route('/api/agent/fip6/agents/redacteurs', name: 'api_agent_agents_liste_redacteurs', methods: ['GET'])]
#[IsGranted(AgentVoter::ACTION_LISTER_REDACTEURS, message: 'Seuls les agents du Bureau du précontentieux peuvent lister les rédacteurs', statusCode: Response::HTTP_FORBIDDEN)]
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
